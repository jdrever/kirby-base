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
        'order', 'orders',
        'vice_county_recorders', 'vice_county_records'
    ];

    protected PDO $database;
    protected App $kirby;

    /** @var PDO|null Cached static database connection for fast lookups */
    private static ?PDO $staticDb = null;

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
                vernacular_name,
                description,
                keywords,
                main_content,
                additional_content,
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

            CREATE TABLE page_lookup (
                ddb_id TEXT PRIMARY KEY,
                page_id TEXT NOT NULL
            );
            CREATE INDEX idx_page_lookup_page_id ON page_lookup(page_id);
        ');
    }

    /**
     * Get additional field values for a page based on its template
     *
     * @param Page $page The page to get additional fields for
     * @return string Combined additional field values
     */
    private function getAdditionalFieldsContent(Page $page): string
    {
        $additionalFieldsConfig = option('search.additionalFields', []);
        $templateName = $page->template()->name();

        if (!isset($additionalFieldsConfig[$templateName])) {
            return '';
        }

        $fieldNames = explode(',', $additionalFieldsConfig[$templateName]);
        $content = $page->content();
        $values = [];

        foreach ($fieldNames as $fieldName) {
            $fieldName = trim($fieldName);
            if (!empty($fieldName)) {
                $value = (string)($content->$fieldName()->value() ?? '');
                if (!empty($value)) {
                    $values[] = strip_tags($value);
                }
            }
        }

        return implode(' ', $values);
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

        // Get additional fields based on template
        $additionalContent = $this->getAdditionalFieldsContent($page);

        $stmt = $this->database->prepare('
            INSERT INTO search_index (page_id, title, vernacular_name, description, keywords, main_content, additional_content, url, is_members_only, template, last_updated)
            VALUES (:page_id, :title, :vernacular_name, :description, :keywords, :main_content, :additional_content, :url, :is_members_only, :template, :last_updated)
        ');

        $result = $stmt->execute([
            'page_id' => $page->id(),
            'title' => (string)($content->title()->value() ?? ''),
            'vernacular_name' => (string)($content->vernacularName()->value() ?? ''),
            'description' => (string)($content->description()->value() ?? ''),
            'keywords' => (string)($content->keywords()->value() ?? ''),
            'main_content' => $mainContent,
            'additional_content' => $additionalContent,
            'url' => $page->url(),
            'is_members_only' => $isMembersOnly,
            'template' => $page->template()->name(),
            'last_updated' => date('Y-m-d H:i:s')
        ]);

        // Add ddbId to page_lookup if present
        $ddbId = (string)($content->ddbId()->value() ?? '');
        if (!empty($ddbId)) {
            $this->addPageLookup($ddbId, $page->id());
        }

        return $result;
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
        $result = $stmt->execute(['page_id' => $pageId]);

        // Also remove from page_lookup
        $lookupStmt = $this->database->prepare('DELETE FROM page_lookup WHERE page_id = :page_id');
        $lookupStmt->execute(['page_id' => $pageId]);

        return $result;
    }

    /**
     * Add or update a page lookup entry by ddb_id
     *
     * @param string $ddbId The DDB identifier
     * @param string $pageId The Kirby page ID
     * @return bool True if entry was added/updated
     */
    public function addPageLookup(string $ddbId, string $pageId): bool
    {
        if (empty($ddbId)) {
            return false;
        }
        $stmt = $this->database->prepare('INSERT OR REPLACE INTO page_lookup (ddb_id, page_id) VALUES (:ddb_id, :page_id)');
        return $stmt->execute(['ddb_id' => $ddbId, 'page_id' => $pageId]);
    }

    /**
     * Find a page ID by its ddb_id
     *
     * @param string $ddbId The DDB identifier to look up
     * @return string|null The page ID if found, null otherwise
     */
    public function findPageIdByDdbId(string $ddbId): ?string
    {
        $this->ensurePageLookupTable();
        $stmt = $this->database->prepare('SELECT page_id FROM page_lookup WHERE ddb_id = :ddb_id LIMIT 1');
        $stmt->execute(['ddb_id' => $ddbId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['page_id'] : null;
    }

    /**
     * Ensure the page_lookup table exists (for databases created before this feature)
     */
    private function ensurePageLookupTable(): void
    {
        $result = $this->database->query("SELECT name FROM sqlite_master WHERE type='table' AND name='page_lookup'");
        if ($result->fetch() === false) {
            $this->database->exec('
                CREATE TABLE page_lookup (
                    ddb_id TEXT PRIMARY KEY,
                    page_id TEXT NOT NULL
                );
                CREATE INDEX idx_page_lookup_page_id ON page_lookup(page_id);
            ');
        }
    }

    /**
     * Fast static lookup of page ID by ddb_id
     *
     * Uses a cached database connection for minimal overhead.
     * Call this instead of instantiating SearchIndexHelper for simple lookups.
     *
     * @param string $ddbId The DDB identifier to look up
     * @return string|null The page ID if found, null otherwise
     */
    public static function lookupPageIdByDdbId(string $ddbId): ?string
    {
        if (self::$staticDb === null) {
            $file = kirby()->root('site') . self::DATABASE_PATH;
            if (!F::exists($file)) {
                return null;
            }
            self::$staticDb = new PDO('sqlite:' . $file);
            self::$staticDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        try {
            $stmt = self::$staticDb->prepare('SELECT page_id FROM page_lookup WHERE ddb_id = :ddb_id LIMIT 1');
            $stmt->execute(['ddb_id' => $ddbId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['page_id'] : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Search the index for matching pages
     *
     * @param string $query Search query string
     * @param bool $includeMembersContent Whether to include members-only content
     * @param int $limit Maximum number of results
     * @param int $offset Offset for pagination
     * @param array|null $templates Optional array of template names to filter results
     * @return array{results: array, total: int} Search results with total count
     */
    public function search(string $query, bool $includeMembersContent = false, int $limit = 10, int $offset = 0, ?array $templates = null): array
    {
        $ftsQuery = $this->prepareQuery($query);

        if (empty($ftsQuery)) {
            return ['results' => [], 'total' => 0];
        }

        $membersClause = $includeMembersContent ? '' : "AND is_members_only = '0'";

        // Build template filter clause if templates specified
        $templateClause = '';
        $templateParams = [];
        if ($templates !== null && count($templates) > 0) {
            $placeholders = [];
            foreach ($templates as $i => $template) {
                $placeholder = ':template' . $i;
                $placeholders[] = $placeholder;
                $templateParams[$placeholder] = $template;
            }
            $templateClause = 'AND template IN (' . implode(', ', $placeholders) . ')';
        }

        // Get total count
        $countSql = "
            SELECT COUNT(*) as total
            FROM search_index
            WHERE search_index MATCH :query $membersClause $templateClause
        ";
        $countStmt = $this->database->prepare($countSql);
        $countStmt->bindValue(':query', $ftsQuery, PDO::PARAM_STR);
        foreach ($templateParams as $placeholder => $value) {
            $countStmt->bindValue($placeholder, $value, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
        $total = (int)($countResult['total'] ?? 0);

        // Get results with BM25 ranking and exact-match boost
        // Weights: page_id=0, title=10, vernacular_name=20, description=5, keywords=5, main_content=1, additional_content=5, url=0, is_members_only=0, template=0
        // Exact match on vernacular_name or title gets a large bonus (subtract 100 from score since more negative = better)
        $resultsSql = "
            SELECT
                page_id,
                title,
                description,
                url,
                template,
                bm25(search_index, 0.0, 10.0, 20.0, 5.0, 5.0, 1.0, 5.0, 0.0, 0.0, 0.0)
                - CASE WHEN LOWER(vernacular_name) = LOWER(:exact_query) THEN 100 ELSE 0 END
                - CASE WHEN LOWER(title) = LOWER(:exact_query) THEN 100 ELSE 0 END
                as score
            FROM search_index
            WHERE search_index MATCH :query $membersClause $templateClause
            ORDER BY score
            LIMIT :limit OFFSET :offset
        ";
        $resultsStmt = $this->database->prepare($resultsSql);
        $resultsStmt->bindValue(':query', $ftsQuery, PDO::PARAM_STR);
        $resultsStmt->bindValue(':exact_query', trim($query), PDO::PARAM_STR);
        $resultsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $resultsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($templateParams as $placeholder => $value) {
            $resultsStmt->bindValue($placeholder, $value, PDO::PARAM_STR);
        }
        $resultsStmt->execute();
        $results = $resultsStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'results' => $results,
            'total' => $total
        ];
    }

    /**
     * Search and return all matching page IDs sorted by relevance
     *
     * Returns all matching page IDs without pagination, suitable for use with
     * Kirby's built-in pagination system.
     *
     * @param string $query Search query string
     * @param bool $includeMembersContent Whether to include members-only content
     * @param string|null $templates Optional array of template names to filter results
     * @return array Array of page IDs sorted by relevance
     */
    public function searchAllIds(string $query, bool $includeMembersContent = false, ?string $templates = null): array
    {
        $ftsQuery = $this->prepareQuery($query);

        if (empty($ftsQuery)) {
            return [];
        }

        $membersClause = $includeMembersContent ? '' : "AND is_members_only = '0'";

        // Build template filter clause if templates specified
        $templateClause = '';
        $templateParams = [];
        $templatesAsArray = $templates ? explode(',', $templates) : [];
        if (count($templatesAsArray) > 0) {
            $placeholders = [];
            foreach ($templatesAsArray as $i => $template) {
                $placeholder = ':template' . $i;
                $placeholders[] = $placeholder;
                $templateParams[$placeholder] = $template;
            }
            $templateClause = 'AND template IN (' . implode(', ', $placeholders) . ')';
        }

        // Get all page IDs sorted by BM25 relevance with exact-match boost
        // Weights: page_id=0, title=10, vernacular_name=20, description=5, keywords=5, main_content=1, additional_content=5, url=0, is_members_only=0, template=0
        // Exact match on vernacular_name gets a large bonus (subtract 100 from score since more negative = better)
        $sql = "
            SELECT page_id
            FROM search_index
            WHERE search_index MATCH :query $membersClause $templateClause
            ORDER BY
                bm25(search_index, 0.0, 10.0, 20.0, 5.0, 5.0, 1.0, 5.0, 0.0, 0.0, 0.0)
                - CASE WHEN LOWER(vernacular_name) = LOWER(:exact_query) THEN 100 ELSE 0 END
                - CASE WHEN LOWER(title) = LOWER(:exact_query) THEN 100 ELSE 0 END
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->bindValue(':query', $ftsQuery, PDO::PARAM_STR);
        $stmt->bindValue(':exact_query', trim($query), PDO::PARAM_STR);
        foreach ($templateParams as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value, PDO::PARAM_STR);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
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
            // Clear existing index and page_lookup table
            $this->database->exec('DELETE FROM search_index');
            $this->ensurePageLookupTable();
            $this->database->exec('DELETE FROM page_lookup');

            $count = 0;

            // Use allPages collection (much smaller than site->index())
            // Get both public and members pages by temporarily impersonating admin
            $allPages = $this->kirby->collection('allPages');

            // Prepare statements once for reuse
            $stmt = $this->database->prepare('
                INSERT INTO search_index (page_id, title, vernacular_name, description, keywords, main_content, additional_content, url, is_members_only, template, last_updated)
                VALUES (:page_id, :title, :vernacular_name, :description, :keywords, :main_content, :additional_content, :url, :is_members_only, :template, :last_updated)
            ');

            $lookupStmt = $this->database->prepare('INSERT OR REPLACE INTO page_lookup (ddb_id, page_id) VALUES (:ddb_id, :page_id)');

            foreach ($allPages as $page) {
                try {
                    // Check if page should be indexed (respects EXCLUDED_TEMPLATES)
                    if (!$this->shouldIndex($page)) {
                        continue;
                    }

                    $content = $page->content();
                    $isMembersOnly = str_starts_with($page->id(), 'members') ? '1' : '0';
                    $mainContent = strip_tags((string)($content->mainContent()->value() ?? ''));
                    $additionalContent = $this->getAdditionalFieldsContent($page);

                    $stmt->execute([
                        'page_id' => $page->id(),
                        'title' => (string)($content->title()->value() ?? ''),
                        'vernacular_name' => (string)($content->vernacularName()->value() ?? ''),
                        'description' => (string)($content->description()->value() ?? ''),
                        'keywords' => (string)($content->keywords()->value() ?? ''),
                        'main_content' => $mainContent,
                        'additional_content' => $additionalContent,
                        'url' => $page->url(),
                        'is_members_only' => $isMembersOnly,
                        'template' => $page->template()->name(),
                        'last_updated' => date('Y-m-d H:i:s')
                    ]);

                    // Add ddbId to page_lookup if present
                    $ddbId = (string)($content->ddbId()->value() ?? '');
                    if (!empty($ddbId)) {
                        $lookupStmt->execute(['ddb_id' => $ddbId, 'page_id' => $page->id()]);
                    }

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
