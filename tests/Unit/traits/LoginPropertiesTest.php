<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\models\LoginDetails;
use BSBI\WebBase\traits\LoginProperties;
use PHPUnit\Framework\TestCase;

/**
 * Tests the LoginProperties trait via an anonymous concrete class.
 */
final class LoginPropertiesTest extends TestCase
{
    private function createModel(): object
    {
        return new class {
            use LoginProperties;
        };
    }

    public function testSetAndGetLoginDetails(): void
    {
        $model = $this->createModel();
        $details = new LoginDetails();
        $details->setUserName('alice');

        $model->setLoginDetails($details);

        $this->assertSame($details, $model->getLoginDetails());
        $this->assertSame('alice', $model->getLoginDetails()->getUserName());
    }

    public function testLoginDetailsCanBeReplaced(): void
    {
        $model = $this->createModel();
        $first = new LoginDetails();
        $first->setUserName('alice');
        $model->setLoginDetails($first);

        $second = new LoginDetails();
        $second->setUserName('bob');
        $model->setLoginDetails($second);

        $this->assertSame('bob', $model->getLoginDetails()->getUserName());
    }

    public function testLoginDetailsPreservesState(): void
    {
        $model = $this->createModel();
        $details = new LoginDetails();
        $details->setLoginStatus(true);
        $details->setCSRFToken('token-abc');
        $details->setRedirectPage('/dashboard');

        $model->setLoginDetails($details);

        $this->assertTrue($model->getLoginDetails()->getLoginStatus());
        $this->assertSame('token-abc', $model->getLoginDetails()->getCSRFToken());
        $this->assertSame('/dashboard', $model->getLoginDetails()->getRedirectPage());
    }
}
