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

    /**
     * @return bool
     */
    public function isLoggedIn(): bool {
        return (!empty($this->userId) && !empty($this->userName));
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     * @return $this
     */
    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @return $this
     */
    public function setUserName(string $userName): self
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return void
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function hasEmail() : bool
    {
        return !empty($this->email);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }


}
