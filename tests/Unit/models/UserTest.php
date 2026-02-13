<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testIsLoggedInRequiresBothUserIdAndUserName(): void
    {
        $user = new User('');

        $this->assertFalse($user->isLoggedIn());

        $user->setUserId('123');
        $this->assertFalse($user->isLoggedIn());

        $user->setUserName('Alice');
        $this->assertTrue($user->isLoggedIn());
    }

    public function testRoleGetterSetter(): void
    {
        $user = new User('');
        $user->setRole('admin');

        $this->assertSame('admin', $user->getRole());
    }

    public function testEmailGetterSetter(): void
    {
        $user = new User('');

        $this->assertFalse($user->hasEmail());

        $user->setEmail('alice@example.com');
        $this->assertTrue($user->hasEmail());
        $this->assertSame('alice@example.com', $user->getEmail());
    }
}
