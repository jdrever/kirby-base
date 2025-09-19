<?php

namespace BSBI\WebBase\models;

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

    /**
     * @return string
     */
    function getItemType(): string
    {
        return User::class;
    }

    /**
     * @return string
     */
    function getFilterType(): string
    {
        return BaseFilter::class;
    }
}
