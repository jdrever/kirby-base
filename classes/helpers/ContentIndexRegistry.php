<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

/**
 * Static registry for content index managers.
 *
 * Holds all registered ContentIndexManager instances, keyed by their definition name.
 * Provides lookup by name or by template (for use in page lifecycle hooks).
 *
 * @package BSBI\WebBase\helpers
 */
class ContentIndexRegistry
{
    /** @var array<string, ContentIndexManager> */
    private static array $managers = [];

    /** @var array<string, string[]> Template name => array of index names */
    private static array $templateMap = [];

    /**
     * Register a content index definition.
     *
     * Creates a ContentIndexManager for the definition and stores it in the registry.
     *
     * @param ContentIndexDefinition $definition The index definition to register
     * @throws \Exception If the manager cannot be created
     */
    public static function register(ContentIndexDefinition $definition): void
    {
        $name = $definition->getName();
        $manager = new ContentIndexManager($definition);
        self::$managers[$name] = $manager;

        // Build template -> index name mapping
        foreach ($definition->getTemplates() as $template) {
            if (!isset(self::$templateMap[$template])) {
                self::$templateMap[$template] = [];
            }
            if (!in_array($name, self::$templateMap[$template], true)) {
                self::$templateMap[$template][] = $name;
            }
        }
    }

    /**
     * Get a manager by index name.
     *
     * @param string $name The index name (e.g. 'events')
     * @return ContentIndexManager|null The manager, or null if not registered
     */
    public static function get(string $name): ?ContentIndexManager
    {
        return self::$managers[$name] ?? null;
    }

    /**
     * Get all managers that index pages with the given template.
     *
     * Used by hooks to determine which indexes need updating when a page changes.
     *
     * @param string $template The Kirby template name
     * @return ContentIndexManager[] Array of matching managers
     */
    public static function getManagersForTemplate(string $template): array
    {
        $indexNames = self::$templateMap[$template] ?? [];
        $managers = [];
        foreach ($indexNames as $name) {
            if (isset(self::$managers[$name])) {
                $managers[] = self::$managers[$name];
            }
        }
        return $managers;
    }

    /**
     * Get all registered managers.
     *
     * @return array<string, ContentIndexManager>
     */
    public static function all(): array
    {
        return self::$managers;
    }

    /**
     * Clear all registered managers (primarily for testing).
     */
    public static function clear(): void
    {
        self::$managers = [];
        self::$templateMap = [];
    }
}
