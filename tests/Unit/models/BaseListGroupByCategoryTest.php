<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\BaseFilter;
use BSBI\WebBase\models\BaseList;
use BSBI\WebBase\models\BaseModel;
use PHPUnit\Framework\TestCase;

/**
 * Stub that groups by the item's title as category.
 */
class CategorisingList extends BaseList
{
    /** @var array<int, string> */
    private array $categoryOrder = [];

    public function getListItems(): array
    {
        return $this->list;
    }

    public function getItemType(): string
    {
        return BaseModel::class;
    }

    public function getFilterType(): string
    {
        return BaseFilter::class;
    }

    public function addItem(BaseModel $item): static
    {
        $this->add($item);
        return $this;
    }

    /** @param array<int, string> $order */
    public function setCategoryOrder(array $order): static
    {
        $this->categoryOrder = $order;
        return $this;
    }

    protected function getCategoryForItem(BaseModel $item): string
    {
        return $item->getTitle();
    }

    protected function getCategoryOrder(): array
    {
        return $this->categoryOrder;
    }
}

/**
 * Unit tests for BaseList::groupByCategory().
 */
final class BaseListGroupByCategoryTest extends TestCase
{
    private CategorisingList $list;

    protected function setUp(): void
    {
        $this->list = new CategorisingList();
    }

    private function item(string $title): BaseModel
    {
        return new class($title) extends BaseModel {};
    }

    public function testReturnsEmptyArrayWhenListIsEmpty(): void
    {
        $this->assertSame([], $this->list->groupByCategory());
    }

    public function testGroupsItemsByCategory(): void
    {
        $this->list->addItem($this->item('Alpha'));
        $this->list->addItem($this->item('Beta'));
        $this->list->addItem($this->item('Alpha'));

        $grouped = $this->list->groupByCategory();

        $this->assertArrayHasKey('Alpha', $grouped);
        $this->assertArrayHasKey('Beta', $grouped);
        $this->assertCount(2, $grouped['Alpha']);
        $this->assertCount(1, $grouped['Beta']);
    }

    public function testSortsAlphabeticallyByDefault(): void
    {
        $this->list->addItem($this->item('Zebra'));
        $this->list->addItem($this->item('Apple'));
        $this->list->addItem($this->item('Mango'));

        $keys = array_keys($this->list->groupByCategory());

        $this->assertSame(['Apple', 'Mango', 'Zebra'], $keys);
    }

    public function testRespectsCategoryOrder(): void
    {
        $this->list->setCategoryOrder(['Zebra', 'Apple', 'Mango']);
        $this->list->addItem($this->item('Apple'));
        $this->list->addItem($this->item('Zebra'));
        $this->list->addItem($this->item('Mango'));

        $keys = array_keys($this->list->groupByCategory());

        $this->assertSame(['Zebra', 'Apple', 'Mango'], $keys);
    }

    public function testUncategorisedItemsUseLabelAndAppearLast(): void
    {
        $this->list->addItem($this->item('Alpha'));

        $uncategorised = new class('') extends BaseModel {};
        $this->list->addItem($uncategorised);

        $grouped = $this->list->groupByCategory();
        $keys = array_keys($grouped);

        $this->assertSame('Alpha', $keys[0]);
        $this->assertSame('Other', $keys[1]);
    }

    public function testUncategorisedItemsUseEmptyStringLabel(): void
    {
        $this->list->addItem($this->item('Alpha'));

        $uncategorised = new class('') extends BaseModel {};
        $this->list->addItem($uncategorised);

        $grouped = $this->list->groupByCategory('');
        $keys = array_keys($grouped);

        $this->assertSame('Alpha', $keys[0]);
        $this->assertSame('', $keys[1]);
    }

    public function testCategoriesNotInOrderAppearAlphabeticallyAfterOrderedOnes(): void
    {
        $this->list->setCategoryOrder(['Zebra']);
        $this->list->addItem($this->item('Mango'));
        $this->list->addItem($this->item('Zebra'));
        $this->list->addItem($this->item('Apple'));

        $keys = array_keys($this->list->groupByCategory());

        $this->assertSame(['Zebra', 'Apple', 'Mango'], $keys);
    }
}
