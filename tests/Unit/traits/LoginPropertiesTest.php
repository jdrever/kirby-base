<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\models\LoginDetails;
use BSBI\WebBase\traits\LoginProperties;
use PHPUnit\Framework\TestCase;

/**
 * Tests the LoginProperties trait via an anonymous concrete class.
 *
 * Covers set/get roundtrip, replacement, and state preservation
 * of the LoginDetails object.
 */
final class LoginPropertiesTest extends TestCase
{
    /**
     * Create an anonymous class that uses LoginProperties for testing.
     *
     * @return object
     */
    private function createModel(): object
    {
        return new class {
            use LoginProperties;
        };
    }

    /**
     * Verify LoginDetails can be set and retrieved with its state intact.
     */
    public function testSetAndGetLoginDetails(): void
    {
        $model = $this->createModel();
        $details = new LoginDetails();
        $details->setUserName('alice');

        $model->setLoginDetails($details);

        $this->assertSame($details, $model->getLoginDetails());
        $this->assertSame('alice', $model->getLoginDetails()->getUserName());
    }

    /**
     * Verify LoginDetails can be replaced with a new instance.
     */
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

    /**
     * Verify the full state of LoginDetails is preserved through the trait.
     */
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
