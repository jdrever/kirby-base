<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\models\PrevNextPageNavigation;
use BSBI\WebBase\traits\PrevNextPageProperties;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PrevNextPageProperties trait via an anonymous concrete class.
 */
final class PrevNextPagePropertiesTest extends TestCase
{
    private function createModel(): object
    {
        return new class {
            use PrevNextPageProperties;
        };
    }

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

    public function testSetPrevNextPageNavigationReturnsSelf(): void
    {
        $model = $this->createModel();
        $nav = new PrevNextPageNavigation();

        $this->assertSame($model, $model->setPrevNextPageNavigation($nav));
    }

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
