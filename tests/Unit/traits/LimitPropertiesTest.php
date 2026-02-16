<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\traits\LimitProperties;
use PHPUnit\Framework\TestCase;

/**
 * Tests the LimitProperties trait via an anonymous concrete class.
 */
final class LimitPropertiesTest extends TestCase
{
    private function createModel(): object
    {
        return new class {
            use LimitProperties;
        };
    }

    public function testLimitDefaultsToZero(): void
    {
        $model = $this->createModel();

        $this->assertSame(0, $model->getLimit());
    }

    public function testLimitGetterSetter(): void
    {
        $model = $this->createModel();
        $model->setLimit(25);

        $this->assertSame(25, $model->getLimit());
    }
}
