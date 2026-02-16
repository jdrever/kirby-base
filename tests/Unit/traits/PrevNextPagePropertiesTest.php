<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\models\PrevNextPageNavigation;
use BSBI\WebBase\traits\PrevNextPageProperties;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PrevNextPageProperties trait via an anonymous concrete class.
 *
 * Covers set/get roundtrip, fluent setter return value,
 * and navigation replacement.
 */
final class PrevNextPagePropertiesTest extends TestCase
{
    /**
     * Create an anonymous class that uses PrevNextPageProperties for testing.
     *
     * @return object
     */
    private function createModel(): object
    {
        return new class {
            use PrevNextPageProperties;
        };
    }

    /**
     * Verify PrevNextPageNavigation can be set and retrieved with its state intact.
     */
    public function testSetAndGetPrevNextPageNavigation(): void
    {
        $model = $this->createModel();
        $nav = new PrevNextPageNavigation();
        $nav->setPreviousPageLink('/prev');
        $nav->setNextPageLink('/next');

        $model->setPrevNextPageNavigation($nav);

        $this->assertSame($nav, $model->getPrevNextPageNavigation());
        $this->assertTrue($model->getPrevNextPageNavigation()->hasNavigation());
    }

    /**
     * Verify setPrevNextPageNavigation() returns the same instance for fluent chaining.
     */
    public function testSetPrevNextPageNavigationReturnsSelf(): void
    {
        $model = $this->createModel();
        $nav = new PrevNextPageNavigation();

        $this->assertSame($model, $model->setPrevNextPageNavigation($nav));
    }

    /**
     * Verify the navigation object can be replaced with a new instance.
     */
    public function testNavigationCanBeReplaced(): void
    {
        $model = $this->createModel();

        $first = new PrevNextPageNavigation();
        $first->setPreviousPageLink('/page-1');
        $model->setPrevNextPageNavigation($first);

        $second = new PrevNextPageNavigation();
        $second->setPreviousPageLink('/page-5');
        $model->setPrevNextPageNavigation($second);

        $this->assertSame('/page-5', $model->getPrevNextPageNavigation()->getPreviousPageLink());
    }
}
