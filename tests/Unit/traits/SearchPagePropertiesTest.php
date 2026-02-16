<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\models\WebPageLink;
use BSBI\WebBase\models\WebPageLinks;
use BSBI\WebBase\traits\SearchPageProperties;
use PHPUnit\Framework\TestCase;

/**
 * Tests the SearchPageProperties trait via an anonymous concrete class.
 */
final class SearchPagePropertiesTest extends TestCase
{
    private function createModel(): object
    {
        return new class {
            use SearchPageProperties;
        };
    }

    public function testSearchResultsDefaultToNone(): void
    {
        $model = $this->createModel();

        $this->assertFalse($model->hasSearchResults());
    }

    public function testSearchResultsGetterSetter(): void
    {
        $model = $this->createModel();
        $results = new WebPageLinks();
        $results->addListItem(new WebPageLink('Result', '/result', 'r1', 'page'));

        $model->setSearchResults($results);

        $this->assertTrue($model->hasSearchResults());
        $this->assertSame(1, $model->getSearchResults()->count());
    }

    public function testSpecialSearchTypeGetterSetter(): void
    {
        $model = $this->createModel();

        $this->assertFalse($model->hasSpecialSearchType());

        $model->setSpecialSearchType('image_search');
        $this->assertTrue($model->hasSpecialSearchType());
        $this->assertSame('image_search', $model->getSpecialSearchType());
    }

    public function testContentTypeOptionsGetterSetter(): void
    {
        $model = $this->createModel();

        $this->assertFalse($model->hasContentTypeOptions());

        $options = ['article', 'page', 'event'];
        $model->setContentTypeOptions($options);

        $this->assertTrue($model->hasContentTypeOptions());
        $this->assertSame($options, $model->getContentTypeOptions());
    }

    public function testSelectedContentTypeGetterSetter(): void
    {
        $model = $this->createModel();

        $this->assertSame('', $model->getSelectedContentType());

        $model->setSelectedContentType('article');
        $this->assertSame('article', $model->getSelectedContentType());
    }

    public function testSearchCompletedGetterSetter(): void
    {
        $model = $this->createModel();

        $this->assertFalse($model->hasSearchCompleted());

        $model->setSearchCompleted(true);
        $this->assertTrue($model->hasSearchCompleted());
    }
}
