<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\LoginDetails;
use PHPUnit\Framework\TestCase;

final class LoginDetailsTest extends TestCase
{
    private function createLoginDetails(): LoginDetails
    {
        return new LoginDetails();
    }

    public function testLoginStatusGetterSetter(): void
    {
        $details = $this->createLoginDetails();
        $details->setLoginStatus(true);

        $this->assertTrue($details->getLoginStatus());
    }

    public function testUserNameGetterSetter(): void
    {
        $details = $this->createLoginDetails();
        $details->setUserName('alice');

        $this->assertSame('alice', $details->getUserName());
    }

    public function testLoginMessageGetterSetter(): void
    {
        $details = $this->createLoginDetails();
        $details->setLoginMessage('Invalid password');

        $this->assertSame('Invalid password', $details->getLoginMessage());
    }

    public function testCSRFTokenGetterSetter(): void
    {
        $details = $this->createLoginDetails();
        $details->setCSRFToken('abc123');

        $this->assertSame('abc123', $details->getCSRFToken());
    }

    public function testRedirectPageGetterSetter(): void
    {
        $details = $this->createLoginDetails();

        $this->assertFalse($details->hasRedirectPage());

        $details->setRedirectPage('/dashboard');
        $this->assertTrue($details->hasRedirectPage());
        $this->assertSame('/dashboard', $details->getRedirectPage());
    }

    public function testHasBeenProcessedGetterSetter(): void
    {
        $details = $this->createLoginDetails();

        $this->assertFalse($details->hasBeenProcessed());

        $details->setHasBeenProcessed(true);
        $this->assertTrue($details->hasBeenProcessed());
    }
}
