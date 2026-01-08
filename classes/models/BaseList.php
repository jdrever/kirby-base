<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\traits\ErrorHandling;

/**
 * Base abstract class for managing lists of model objects.
 *
 * This class provides common functionality for storing, filtering, and paginating
 * collections of model objects. It uses a generic type parameter T which should
 * extend BaseModel to ensure type safety across implementations.
 *
 * Features:
 * - Generic type support for list items
 * - Filtering capabilities through BaseFilter
 * - Optional pagination support
 * - Basic list operations (add, get, count)
 * - Item search functionality
 *
 * @template T of BaseModel
 */


abstract class BaseList
{
    use ErrorHandling;

    /**
     * @return T[]
     * @noinspection PhpUnused
     */
    abstract function getListItems(): array;

    /**
     * @return string
     * @noinspection PhpUnused
     */
    abstract function getItemType(): string;

    /**
     * @return string
     * @noinspection PhpUnused
     */
    abstract function getFilterType(): string;

    /** @var T[] $list */
    protected array $list = [];

    protected BaseFilter $filter;

    /**
     * @var Pagination
     */
    private Pagination $pagination;

    private int $paginatePerPage = 10;


    /**
     * Add the item.
     * For implementations, use a specific add function, e.g. addCategory, that enforces type
     * @param T $item
     * @return $this
     * @noinspection PhpDocSignatureInspection
     */
    protected function add(BaseModel $item): BaseList
    {
        $this->list[] = $item;
        return $this;
    }

    /**
     * List the items
     * @return T[]
     * @noinspection PhpUnused
     */
    public function getList(): array
    {
        return $this->list;
    }


    /**
     * @return bool
     */
    public function hasListItems(): bool {
        return count($this->list) > 0;
    }

    /**
     * @param int $x
     * @return array
     */
    public function getFirstXItems(int $x): array {
        return array_slice($this->list, 0, $x);
    }

    /**
     * @param int $index
     * @return array
     */
    public function getItemsFromIndex(int $index): array {
        return array_slice($this->list, $index-1);
    }

    /**
     * count categories
     * @return int the number of categories
     */
    public function count(): int
    {
        return count($this->list);
    }

    /**
     * Find the item with a matching name
     * Will return null if no item found
     * @param string $title
     * @return BaseModel|null
     * @noinspection PhpUnused
     */
    protected function findItemByTitle(string $title): BaseModel|null
    {
        // Function to find the matching object
        $matchingItem = array_filter($this->list, function ($item) use ($title) {
            return $item->getTitle() === $title;
        });

        // Get the first matching result, if any
        $matchingItem = reset($matchingItem);

        if ($matchingItem) {
            return $matchingItem;
        } else {
            return null;
        }
    }


    /**
     * the default sort for the list (by title, intended to be overriden
     * for specific lists)
     * @return $this
     */
    public function sort(): static
    {
        return $this->sortByTitle();
    }

    /**
     * Sorts the list by the title of the items.
     *
     * @param bool $descending Optional. If true, sorts in descending order. Default is false (ascending).
     * @return $this
     * @noinspection PhpUnused
     */
    public function sortByTitle(bool $descending = false): static
    {
        return $this->sortBy(fn($item) => $item->getTitle(), true);
    }

    /**
     * @param callable(T): mixed $callback
     * @param bool $descending
     * @return $this
     */
    protected function sortBy(callable $callback, bool $descending = false): static
    {
        usort($this->list, function (BaseModel $a, BaseModel $b) use ($callback, $descending) {
            $valA = $callback($a);
            $valB = $callback($b);

            $comparison = $valA <=> $valB;
            return $descending ? -$comparison : $comparison;
        });

        return $this;
    }

    /**
     * @return bool
     */
    public function usePagination(): bool {
        return false;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function hasPagination(): bool {
        return isset($this->pagination);
    }

    /**
     * @return Pagination
     * @noinspection PhpUnused
     */
    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    /**
     * @param Pagination $pagination
     * @return $this
     */
    public function setPagination(Pagination $pagination): static
    {
        $this->pagination = $pagination;
        return $this;
    }

    /**
     * @return int
     */
    public function getPaginatePerPage(): int
    {
        return $this->paginatePerPage;
    }

    /**
     * @param int $paginatePerPage
     * @return BaseList
     * @noinspection PhpUnused
     */
    public function setPaginatePerPage(int $paginatePerPage): BaseList
    {
        $this->paginatePerPage = $paginatePerPage;
        return $this;
    }

    /**
     * @return BaseFilter
     * @noinspection PhpUnused
     */
    public function getFilters(): BaseFilter
    {
        return $this->filter;
    }

    /**
     * @param BaseFilter $filter
     * @return $this
     */
    public function setFilters(BaseFilter $filter): static
    {
        $this->filter = $filter;
        return $this;
    }


}