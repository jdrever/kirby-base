<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

use Kirby\Cms\Structure;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Str;

/**
 * Service for filtering Kirby Collection and Structure objects.
 * All methods are pure (no side effects) and operate only on the
 * Kirby Collection/Structure API and KirbyFieldReader.
 */
final readonly class CollectionFilterService
{
    /**
     * @param KirbyFieldReader $fieldReader For field-value access on referenced pages
     */
    public function __construct(private KirbyFieldReader $fieldReader)
    {
    }

    /**
     * Filter a collection to pages where a pages-type field contains a page with the given title.
     *
     * @param Collection $pages
     * @param string $tagName
     * @param string $tagValue
     * @return Collection
     * @noinspection PhpUnused
     */
    public function filterByPagesTag(Collection $pages, string $tagName, string $tagValue): Collection
    {
        if (empty($tagValue)) {
            return $pages;
        }
        return $pages->filter(function ($page) use ($tagName, $tagValue) {
            $linkedPages = $page->content()->get($tagName)->toPages();
            return ($linkedPages->filterBy('title', $tagValue)->count() > 0);
        });
    }

    /**
     * Filter a structure to items where a pages-type field contains a page with the given title.
     *
     * @param Structure $structure
     * @param string $tagName
     * @param string $tagValue
     * @return Structure
     * @noinspection PhpUnused
     */
    public function filterStructureByPagesTag(Structure $structure, string $tagName, string $tagValue): Structure
    {
        if (empty($tagValue)) {
            return $structure;
        }
        return $structure->filter(function ($structureItem) use ($tagName, $tagValue) {
            $linkedPages = $structureItem->content()->get($tagName)->toPages();
            if ($linkedPages->isNotEmpty()) {
                return ($linkedPages->filterBy('title', $tagValue)->count() > 0);
            }
            return false;
        });
    }

    /**
     * Filter a collection to pages where a field value exactly equals the given value.
     *
     * @param Collection $pages
     * @param string $fieldName
     * @param string $value
     * @return Collection
     */
    public function filterByEqualsValue(Collection $pages, string $fieldName, string $value): Collection
    {
        return $pages->filter(function ($page) use ($fieldName, $value) {
            return $page->content()->{$fieldName}()->value() === $value;
        });
    }

    /**
     * Filter a collection to pages where a comma-separated field contains the given value.
     *
     * @param Collection $pages
     * @param string $fieldName
     * @param string $value
     * @return Collection
     */
    public function filterByContainsValue(Collection $pages, string $fieldName, string $value): Collection
    {
        return $pages->filter(function ($page) use ($fieldName, $value) {
            $field = $page->{$fieldName}();
            if ($field->isNotEmpty()) {
                $values = Str::split($field->value(), ', ');
                return in_array($value, $values, true);
            }
            return false;
        });
    }

    /**
     * Filter a collection to pages where a comma-separated field contains any of the given values (OR logic).
     *
     * @param Collection $pages
     * @param string $fieldName
     * @param array $values
     * @param bool $includeIfEmpty Return pages where the field is empty
     * @return Collection
     */
    public function filterByContainsValues(
        Collection $pages,
        string     $fieldName,
        array      $values,
        bool       $includeIfEmpty = false
    ): Collection {
        $targetValues = array_map('trim', $values);
        return $pages->filter(function ($page) use ($fieldName, $targetValues, $includeIfEmpty) {
            $field = $page->{$fieldName}();
            if ($field->isNotEmpty()) {
                $fieldValues = array_map('trim', $field->split(','));
                return count(array_intersect($targetValues, $fieldValues)) > 0;
            }
            return $includeIfEmpty;
        });
    }

    /**
     * Filter a collection to pages where a pages-type field references pages whose
     * sub-field title exactly matches the given value.
     *
     * @param Collection $pages
     * @param string $fieldName The pages-type field on each page
     * @param string $pageFieldName The field on the referenced page to check
     * @param string $value The value to match
     * @return Collection
     */
    public function filterByContainsPageTitle(
        Collection $pages,
        string     $fieldName,
        string     $pageFieldName,
        string     $value
    ): Collection {
        return $pages->filter(function ($page) use ($fieldName, $pageFieldName, $value) {
            $fieldPages = $page->{$fieldName}()->toPages();
            foreach ($fieldPages as $fieldPage) {
                if ($this->fieldReader->isPageFieldNotEmpty($fieldPage, $pageFieldName)
                    && $this->fieldReader->getPageFieldAsPageTitle($fieldPage, $pageFieldName) === $value) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Filter a collection to pages where a pages-type field references pages whose
     * sub-field title matches any of the given values (OR logic).
     *
     * @param Collection $pages
     * @param string $fieldName The pages-type field on each page
     * @param string $pageFieldName The field on the referenced page to check
     * @param string[] $values The values to match against
     * @return Collection
     */
    public function filterByContainsPageTitleAny(
        Collection $pages,
        string     $fieldName,
        string     $pageFieldName,
        array      $values
    ): Collection {
        return $pages->filter(function ($page) use ($fieldName, $pageFieldName, $values) {
            $fieldPages = $page->{$fieldName}()->toPages();
            foreach ($fieldPages as $fieldPage) {
                if ($this->fieldReader->isPageFieldNotEmpty($fieldPage, $pageFieldName)
                    && in_array($this->fieldReader->getPageFieldAsPageTitle($fieldPage, $pageFieldName), $values, true)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Filter a collection to pages where a pages-type field references pages
     * whose own title matches any of the given values (OR logic).
     *
     * Unlike filterByContainsPageTitleAny, this checks the referenced pages' own
     * title rather than a sub-field on the referenced page.
     *
     * @param Collection $pages
     * @param string $fieldName The pages-type field on each page
     * @param string[] $values The titles to match against
     * @return Collection
     */
    public function filterByLinkedPageTitleAny(Collection $pages, string $fieldName, array $values): Collection
    {
        return $pages->filter(function ($page) use ($fieldName, $values) {
            $fieldPages = $page->{$fieldName}()->toPages();
            foreach ($fieldPages as $fieldPage) {
                if (in_array($fieldPage->title()->toString(), $values, true)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Apply a limit to a collection (limit of 0 or less means no limit applied).
     *
     * @param Collection $pages
     * @param int $limit
     * @return Collection
     * @noinspection PhpUnused
     */
    public function applyLimit(Collection $pages, int $limit): Collection
    {
        if ($limit > 0) {
            $pages = $pages->limit($limit);
        }
        return $pages;
    }
}
