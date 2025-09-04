<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\models\BaseList;

/**
 * Represents a list of User objects, extending the BaseList functionality.
 * Provides methods to retrieve, add, and manage Users within the list.
 */
class UserList extends BaseList
{

    /**
     * @return User[]
     */
    public function getListItems(): array
    {
        return $this->list;
    }

    /**
     * @param User $user
     */
    public function addListItem(User $user): void
    {
        $this->add($user);
    }

    function getItemType(): string
    {
        return User::class;
    }

    function getFilterType(): string
    {
        return BaseFilter::class;
    }
}
