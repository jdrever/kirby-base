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
     */
    abstract function getListItems(): array;

    abstract function getItemType(): string;

    abstract function getFilterType(): string;

    /** @var T[] $list */
    protected array $list = [];

    protected BaseFilter $filter;

    /**
     * @var Pagination
     */
    private Pagination $pagination;


    /**
     * Add the item.
     * For implementations, use a specific add function, e.g. addCategory, that enforces type
     * @param T $item
     * @return $this
     */
    protected function add(BaseModel $item): static
    {
        $this->list[] = $item;
        return $this;
    }

    /**
     * List the items
     *
     * @return BaseModel
     */
    public function getList(): array
    {
        return $this->list;
    }


    public function hasListItems(): bool {
        return count($this->list) > 0;
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
     * @return bool
     */
    public function usePagination(): bool {
        return false;
    }

    /**
     * @return bool
     */
    public function hasPagination(): bool {
        return isset($this->pagination);
    }

    /**
     * @return Pagination
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
     * @return BaseFilter
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