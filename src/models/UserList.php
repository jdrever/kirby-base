<?php

namespace BSBI\WebBase\models;

use BSBI\WebBase\traits\ListHandling;


/**
 * Represents a submission list
 *
 * @package BSBI\Web
 */
class UserList
{
    /**
     * @use ListHandling<User,BaseFilter>
     */
    use ListHandling;

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->list;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user): void
    {
        $this->add($user);
    }

}
