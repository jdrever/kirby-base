<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\LoginDetails;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the LoginDetails model.
 *
 * Covers login status, username, login message, CSRF token,
 * redirect page, and processing state getters and setters.
 */
final class LoginDetailsTest extends TestCase
{
    /**
     * Create a LoginDetails instance for testing.
     *
     * @return LoginDetails
     */
    private function createLoginDetails(): LoginDetails
    {
        return new LoginDetails();
    }

    /**
     * Verify login status can be set and retrieved.
     */
    public function testLoginStatusGetterSetter(): void
    {
        $details = $this->createLoginDetails();
        $details->setLoginStatus(true);

        $this->assertTrue($details->getLoginStatus());
    }

    /**
     * Verify username can be set and retrieved.
     */
    public function testUserNameGetterSetter(): void
    {
        $details = $this->createLoginDetails();
        $details->setUserName('alice');

        $this->assertSame('alice', $details->getUserName());
    }

    /**
     * Verify login message can be set and retrieved.
     */
    public function testLoginMessageGetterSetter(): void
    {
        $details = $this->createLoginDetails();
        $details->setLoginMessage('Invalid password');

        $this->assertSame('Invalid password', $details->getLoginMessage());
    }

    /**
     * Verify CSRF token can be set and retrieved.
     */
    public function testCSRFTokenGetterSetter(): void
    {
        $details = $this->createLoginDetails();
        $details->setCSRFToken('abc123');

        $this->assertSame('abc123', $details->getCSRFToken());
    }

    /**
     * Verify redirect page defaults to empty and can be set.
     */
    public function testRedirectPageGetterSetter(): void
    {
        $details = $this->createLoginDetails();

        $this->assertFalse($details->hasRedirectPage());

        $details->setRedirectPage('/dashboard');
        $this->assertTrue($details->hasRedirectPage());
        $this->assertSame('/dashboard', $details->getRedirectPage());
    }

    /**
     * Verify processing state defaults to false and can be toggled.
     */
    public function testHasBeenProcessedGetterSetter(): void
    {
        $details = $this->createLoginDetails();

        $this->assertFalse($details->hasBeenProcessed());

        $details->setHasBeenProcessed(true);
        $this->assertTrue($details->hasBeenProcessed());
    }
}
