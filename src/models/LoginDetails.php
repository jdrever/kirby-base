<?php

namespace BSBI\WebBase\models;

/**
 * Class LoginDetails
 * Handles login-related information and state for authentication processes.
 *
 * This class stores login attempt details including:
 * - Login success/failure status
 * - Username of the login attempt
 * - Messages related to login results
 * - CSRF token for form security
 *
 * @package BSBI\WebBase\models
 */
class LoginDetails
{
    
    /**
     * Indicates whether the login attempt has failed or succeeded
     * @var bool
     */
    private bool $loginStatus;

    private bool $hasBeenProcessed = false;

    /**
     * The username provided during login attempt
     * @var string
     */
    private string $userName = '';

    /**
     * Message to display to user about login attempt result
     * @var string
     */
    private string $loginMessage = '';

    /**
     * CSRF token for form security validation
     * @var string
     */
    private string $CSRFToken = '';

    private string $redirectPage = '';

    /**
     * @return bool
     */
    public function getLoginStatus(): bool
    {
        return $this->loginStatus;
    }

    /**
     * @param bool $loginFailed
     * @return void
     */
    public function setLoginStatus(bool $loginStatus): void
    {
        $this->loginStatus = $loginStatus;
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
     * @return void
     */
    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }


    /**
     * @return string
     */
    public function getLoginMessage(): string
    {
        return $this->loginMessage;
    }

    /**
     * @param string $loginMessage
     * @return void
     */
    public function setLoginMessage(string $loginMessage): void
    {
        $this->loginMessage = $loginMessage;
    }

    /**
     * @return string
     */
    public function getCSRFToken(): string
    {
        return $this->CSRFToken;
    }

    /**
     * @param string $CSRFToken
     * @return void
     */
    public function setCSRFToken(string $CSRFToken): void
    {
        $this->CSRFToken = $CSRFToken;
    }

    /**
     * @return string
     */
    public function getRedirectPage(): string
    {
        return $this->redirectPage;
    }

    /**
     * @param string $redirectPage
     * @return LoginDetails
     */
    public function setRedirectPage(string $redirectPage): LoginDetails
    {
        $this->redirectPage = $redirectPage;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasBeenProcessed(): bool
    {
        return $this->hasBeenProcessed;
    }

    /**
     * @param bool $hasBeenProcessed
     * @return LoginDetails
     */
    public function setHasBeenProcessed(bool $hasBeenProcessed): LoginDetails
    {
        $this->hasBeenProcessed = $hasBeenProcessed;
        return $this;
    }

}
