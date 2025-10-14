<?php /** @noinspection PhpDocFieldTypeMismatchInspection */
/** @noinspection PhpDeprecationInspection */


namespace BSBI\WebBase\traits;

use BSBI\WebBase\models\BaseFilter;
use BSBI\WebBase\models\BaseModel;
use BSBI\WebBase\models\Pagination;

/**
 * @deprecated
 * Use BaseList instead
 * @template T of BaseModel
 * @template U of BaseFilter
 *
 * @package BSBI\Web
 */
trait ListHandling
{
    /** @var T[] $list */
    protected array $list = [];

    /** @var U $filter
     * @noinspection PhpDocFieldTypeMismatchInspection
     */
    protected BaseFilter $filter;

    /**
     * @var Pagination
     */
    private Pagination $pagination;


    /**
     * Add the item.
     * For implementations, use a specific add function, e.g. addCategory, that enforces type
     * @param T $item
     * @return static
     * @noinspection PhpDocSignatureInspection
     */
    protected function add(BaseModel $item): static
    {
        $this->list[] = $item;
        return $this;
    }

    /**
     * List the items
     *
     * @return T[]
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
    public function setPagination(Pagination $pagination): self
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
    public function setFilters(BaseFilter $filter): self
    {
        $this->filter = $filter;
        return $this;
    }



}