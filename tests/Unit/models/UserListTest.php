<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\User;
use BSBI\WebBase\models\UserList;
use PHPUnit\Framework\TestCase;

final class UserListTest extends TestCase
{
    private function createUser(string $title = 'Alice'): User
    {
        return new User($title);
    }

    public function testAddAndRetrieveUsers(): void
    {
        $list = new UserList();
        $user1 = $this->createUser('Alice');
        $user2 = $this->createUser('Bob');

        $list->addListItem($user1);
        $list->addListItem($user2);

        $this->assertSame(2, $list->count());
        $this->assertSame($user1, $list->getListItems()[0]);
        $this->assertSame($user2, $list->getListItems()[1]);
    }

    public function testEmptyByDefault(): void
    {
        $list = new UserList();

        $this->assertSame(0, $list->count());
        $this->assertFalse($list->hasListItems());
    }

    public function testSortByTitleDescending(): void
    {
        $list = new UserList();
        $list->addListItem($this->createUser('Alice'));
        $list->addListItem($this->createUser('Charlie'));
        $list->addListItem($this->createUser('Bob'));

        $list->sortByTitle();

        $items = $list->getListItems();
        $this->assertSame('Charlie', $items[0]->getTitle());
        $this->assertSame('Bob', $items[1]->getTitle());
        $this->assertSame('Alice', $items[2]->getTitle());
    }
}
