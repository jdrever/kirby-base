<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\models\WebPageLink;
use PHPUnit\Framework\TestCase;

/**
 * Tests the ErrorHandling trait via WebPageLink (a concrete BaseModel subclass).
 */
final class ErrorHandlingTest extends TestCase
{
    private function createModel(): WebPageLink
    {
        return new WebPageLink('Test', '/test', 'test', 'default');
    }

    public function testNewModelHasNoErrors(): void
    {
        $model = $this->createModel();

        $this->assertTrue($model->getStatus());
        $this->assertTrue($model->didComplete());
        $this->assertFalse($model->didNotComplete());
        $this->assertFalse($model->hasErrors());
        $this->assertSame([], $model->getErrorMessages());
    }

    public function testRecordErrorSetsStatusAndMessage(): void
    {
        $model = $this->createModel();
        $model->recordError('Something went wrong', 'Please try again');

        $this->assertFalse($model->getStatus());
        $this->assertTrue($model->hasErrors());
        $this->assertTrue($model->didNotComplete());
        $this->assertSame('Something went wrong', $model->getFirstErrorMessage());
        $this->assertSame('Please try again', $model->getFirstFriendlyMessage());
    }

    public function testRecordErrorWithoutFriendlyMessage(): void
    {
        $model = $this->createModel();
        $model->recordError('Internal error');

        $this->assertTrue($model->hasErrors());
        $this->assertFalse($model->hasFriendlyMessages());
    }

    public function testRecordErrorsAccumulatesMessages(): void
    {
        $model = $this->createModel();
        $model->recordErrors(
            ['Error 1', 'Error 2'],
            ['Fix 1', 'Fix 2']
        );

        $this->assertCount(2, $model->getErrorMessages());
        $this->assertCount(2, $model->getFriendlyMessages());
        $this->assertSame('Error 1,Error 2', $model->getErrorMessagesAsString());
    }

    public function testAddErrorMessageAccumulates(): void
    {
        $model = $this->createModel();
        $model->addErrorMessage('First');
        $model->addErrorMessage('Second');

        $this->assertCount(2, $model->getErrorMessages());
        $this->assertSame('First', $model->getFirstErrorMessage());
    }

    public function testGetFirstErrorMessageReturnsEmptyWhenNoErrors(): void
    {
        $model = $this->createModel();
        $this->assertSame('', $model->getFirstErrorMessage());
    }

    public function testCriticalErrorDefaultsToTrue(): void
    {
        $model = $this->createModel();
        $this->assertTrue($model->isCriticalError());

        $model->setIsCriticalError(false);
        $this->assertFalse($model->isCriticalError());
    }
}
