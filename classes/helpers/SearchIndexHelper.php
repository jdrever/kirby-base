<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PDO;
use Throwable;

/**
 * SQLite FTS5-based search index manager
 *
 * Handles indexing, querying, and maintenance of the full-text search database.
 */
class SearchIndexHelper
{
    private const string DATABASE_PATH = '/logs/search/search.sqlite';

    private const array STOP_WORDS = [
        'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
        'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been',
        'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
        'could', 'should', 'may', 'might', 'must', 'shall', 'can', 'need',
        'this', 'that', 'these', 'those', 'it', 'its'
    ];

    private const array EXCLUDED_TEMPLATES = [
        'basket', 'basket_item', 'basket_page', 'blog_month_folder',
        'blog_year_folder', 'checkout', 'error', 'error-500',
        'file_archive', 'form_training', 'image_bank', 'login',
        'vice_county_recorders', 'vice_county_records'
    ];

    protected PDO $database;
    protected App $kirby;

    /**
     * Constructor - initializes database connection
     *
     * @throws KirbyRetrievalException If database cannot be initialized
     */
    public function __construct()
    {
        $this->kirby = kirby();
        $this->initializeDatabase();
    }

    /**
     * Initialize database connection, creating file and schema if needed
     *
     * @throws KirbyRetrievalException If database cannot be created or connected
     */
    private function initializeDatabase(): void
    {
        $file = $this->kirby->root('site') . self::DATABASE_PATH;

        if (F::exists($file) === false) {
            $dir = dirname($file);
            if (is_dir($dir) === false) {
                Dir::make($dir);
            }
            $this->createDatabase($file);
        }

        $this->database = new PDO('sqlite:' . $file);
        $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Create the SQLite database with FTS5 schema
     *
     * @param string $file Path to database file
     */
    private function createDatabase(string $file): void
    {
        $pdo = new PDO('sqlite:' . $file);
        $pdo->exec('
            CREATE VIRTUAL TABLE search_index USING fts5(
                page_id,
                title,
                description,
                keywords,
                main_content,
                url,
                is_members_only,
                template,
                last_updated UNINDEXED,
                tokenize="porter unicode61"
            );

            CREATE TABLE search_meta (
                key TEXT PRIMARY KEY,
                value TEXT
            );
        ');
    }

    /**
     * Index a single page in the search database
     *
     * @param Page $page The Kirby page to index
     * @return bool True if page was indexed, false if skipped
     */
    public function indexPage(Page $page): bool
    {
        // Remove existing entry first
        $this->removePage($page->id());

        // Check if page should be indexed
        if (!$this->shouldIndex($page)) {
            return false;
        }

        $content = $page->content();
        $isMembersOnly = str_starts_with($page->id(), 'members') ? '1' : '0';

        // Strip HTML from main content
        $mainContent = strip_tags((string)($content->mainContent()->value() ?? ''));

        $stmt = $this->database->prepare('
            INSERT INTO search_index (page_id, title, description, keywords, main_content, url, is_members_only, template, last_updated)
            VALUES (:page_id, :title, :description, :keywords, :main_content, :url, :is_members_only, :template, :last_updated)
        ');

        return $stmt->execute([
            'page_id' => $page->id(),
            'title' => (string)($content->title()->value() ?? ''),
            'description' => (string)($content->description()->value() ?? ''),
            'keywords' => (string)($content->keywords()->value() ?? ''),
            'main_content' => $mainContent,
            'url' => $page->url(),
            'is_members_only' => $isMembersOnly,
            'template' => $page->template()->name(),
            'last_updated' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Remove a page from the search index
     *
     * @param string $pageId The Kirby page ID to remove
     * @return bool True if deletion was executed
     */
    public function removePage(string $pageId): bool
    {
        $stmt = $this->database->prepare('DELETE FROM search_index WHERE page_id = :page_id');
        return $stmt->execute(['page_id' => $pageId]);
    }

    /**
     * Search the index for matching pages
     *
     * @param string $query Search query string
     * @param bool $includeMembersContent Whether to include members-only content
     * @param int $limit Maximum number of results
     * @param int $offset Offset for pagination
     * @return array{results: array, total: int} Search results with total count
     */
    public function search(string $query, bool $includeMembersContent = false, int $limit = 10, int $offset = 0): array
    {
        $ftsQuery = $this->prepareQuery($query);

        if (empty($ftsQuery)) {
            return ['results' => [], 'total' => 0];
        }

        $membersClause = $includeMembersContent ? '' : "AND is_members_only = '0'";

        // Get total count
        $countStmt = $this->database->prepare("
            SELECT COUNT(*) as total
            FROM search_index
            WHERE search_index MATCH :query $membersClause
        ");
        $countStmt->execute(['query' => $ftsQuery]);
        $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
        $total = (int)($countResult['total'] ?? 0);

        // Get results with BM25 ranking
        // Weights: title=10, description=5, keywords=5, main_content=1, others=0
        $resultsStmt = $this->database->prepare("
            SELECT
                page_id,
                title,
                description,
                url,
                template,
                bm25(search_index, 0.0, 10.0, 5.0, 5.0, 1.0, 0.0, 0.0, 0.0, 0.0) as score
            FROM search_index
            WHERE search_index MATCH :query $membersClause
            ORDER BY score
            LIMIT :limit OFFSET :offset
        ");
        $resultsStmt->bindValue(':query', $ftsQuery, PDO::PARAM_STR);
        $resultsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $resultsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $resultsStmt->execute();
        $results = $resultsStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'results' => $results,
            'total' => $total
        ];
    }

    /**
     * Prepare query string for FTS5 matching
     *
     * Handles quoted phrases and filters stop words.
     *
     * @param string $query Raw search query
     * @return string FTS5-compatible query string
     */
    private function prepareQuery(string $query): string
    {
        $query = trim($query);
        if (empty($query)) {
            return '';
        }

        // Extract quoted phrases
        preg_match_all('/"([^"]+)"/', $query, $phraseMatches);
        $phrases = $phraseMatches[0] ?? [];

        // Get remaining words after removing quoted phrases
        $remaining = preg_replace('/"[^"]+"/', '', $query);
        $words = preg_split('/\s+/', trim($remaining ?? ''));

        if ($words === false) {
            $words = [];
        }

        // Filter stop words and empty strings
        $filteredWords = array_filter($words, fn($word) =>
            strlen($word) > 2 && !in_array(strtolower($word), self::STOP_WORDS)
        );

        // Build FTS5 query with prefix matching for individual words
        $parts = array_merge($phrases, array_map(fn($w) => $w . '*', $filteredWords));

        return implode(' ', $parts);
    }

    /**
     * Check if a page should be indexed
     *
     * @param Page $page The page to check
     * @return bool True if page should be indexed
     */
    private function shouldIndex(Page $page): bool
    {
        // Exclude certain templates
        if (in_array($page->template()->name(), self::EXCLUDED_TEMPLATES)) {
            return false;
        }

        // Exclude Fermanagh species accounts subfolder
        if (str_starts_with($page->id(), 'in-your-area/local-botany/co-fermanagh/fermanagh-species-accounts')) {
            return false;
        }

        // Only index published/listed pages
        if (!$page->isListed() && !$page->isUnlisted()) {
            return false;
        }

        return true;
    }

    /**
     * Rebuild the entire search index using the allPages collection
     *
     * @return int Number of pages indexed
     */
    public function rebuildIndex(): int
    {
        // Use transaction for much better performance
        $this->database->beginTransaction();

        try {
            // Clear existing index
            $this->database->exec('DELETE FROM search_index');

            $count = 0;

            // Use allPages collection (much smaller than site->index())
            // Get both public and members pages by temporarily impersonating admin
            $allPages = $this->kirby->collection('allPages');

            // Prepare statement once for reuse
            $stmt = $this->database->prepare('
                INSERT INTO search_index (page_id, title, description, keywords, main_content, url, is_members_only, template, last_updated)
                VALUES (:page_id, :title, :description, :keywords, :main_content, :url, :is_members_only, :template, :last_updated)
            ');

            foreach ($allPages as $page) {
                try {
                    $content = $page->content();
                    $isMembersOnly = str_starts_with($page->id(), 'members') ? '1' : '0';
                    $mainContent = strip_tags((string)($content->mainContent()->value() ?? ''));

                    $stmt->execute([
                        'page_id' => $page->id(),
                        'title' => (string)($content->title()->value() ?? ''),
                        'description' => (string)($content->description()->value() ?? ''),
                        'keywords' => (string)($content->keywords()->value() ?? ''),
                        'main_content' => $mainContent,
                        'url' => $page->url(),
                        'is_members_only' => $isMembersOnly,
                        'template' => $page->template()->name(),
                        'last_updated' => date('Y-m-d H:i:s')
                    ]);
                    $count++;
                } catch (Throwable $e) {
                    error_log('Failed to index page ' . $page->id() . ': ' . $e->getMessage());
                }
            }

            // Update metadata
            $metaStmt = $this->database->prepare('INSERT OR REPLACE INTO search_meta (key, value) VALUES (:key, :value)');
            $metaStmt->execute(['key' => 'last_rebuild', 'value' => date('Y-m-d H:i:s')]);

            $this->database->commit();
        } catch (Throwable $e) {
            $this->database->rollBack();
            throw $e;
        }

        return $count;
    }

    /**
     * Get search index statistics
     *
     * @return array{total_pages: int, last_rebuild: string|null}
     */
    public function getStats(): array
    {
        $countStmt = $this->database->query('SELECT COUNT(*) as total FROM search_index');
        $count = $countStmt->fetch(PDO::FETCH_ASSOC);

        $metaStmt = $this->database->prepare('SELECT value FROM search_meta WHERE key = :key');
        $metaStmt->execute(['key' => 'last_rebuild']);
        $lastRebuild = $metaStmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_pages' => (int)($count['total'] ?? 0),
            'last_rebuild' => $lastRebuild['value'] ?? null
        ];
    }
}
