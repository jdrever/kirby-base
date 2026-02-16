<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\traits\LimitProperties;
use PHPUnit\Framework\TestCase;

/**
 * Tests the LimitProperties trait via an anonymous concrete class.
 *
 * Covers default zero value and getter/setter.
 */
final class LimitPropertiesTest extends TestCase
{
    /**
     * Create an anonymous class that uses LimitProperties for testing.
     *
     * @return object
     */
    private function createModel(): object
    {
        return new class {
            use LimitProperties;
        };
    }

    /**
     * Verify limit defaults to zero.
     */
    public function testLimitDefaultsToZero(): void
    {
        $model = $this->createModel();

        $this->assertSame(0, $model->getLimit());
    }

    /**
     * Verify limit can be set and retrieved.
     */
    public function testLimitGetterSetter(): void
    {
        $model = $this->createModel();
        $model->setLimit(25);

        $this->assertSame(25, $model->getLimit());
    }
}
