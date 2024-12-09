<?php

namespace BSBI\WebBase\models;

/**
 * Class Person
 * Represents a user of the website
 *
 * @package BSBI\Web
 */
class User extends BaseModel
{

    /** @var string */
    private string $userId;

    /** @var string */
    private string $userName;

    private string $role;

    private string $email;

    public function isLoggedIn(): bool {
        return (!empty($this->userId) && !empty($this->userName));
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): self
    {
        $this->userName = $userName;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }


}
