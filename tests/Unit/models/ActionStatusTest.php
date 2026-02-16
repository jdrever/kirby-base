<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\helpers\KirbyRetrievalException;
use BSBI\WebBase\models\ActionStatus;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the ActionStatus model.
 *
 * Covers successful/failed status construction, error and friendly
 * message storage, empty message handling, and exception storage.
 */
final class ActionStatusTest extends TestCase
{
    /**
     * Verify a successful ActionStatus has no errors and reports completion.
     */
    public function testSuccessfulStatus(): void
    {
        $status = new ActionStatus(true);

        $this->assertTrue($status->getStatus());
        $this->assertTrue($status->didComplete());
        $this->assertFalse($status->hasErrors());
    }

    /**
     * Verify a failed ActionStatus stores both error and friendly messages.
     */
    public function testFailedStatusWithMessages(): void
    {
        $status = new ActionStatus(false, 'DB error', 'Something went wrong');

        $this->assertFalse($status->getStatus());
        $this->assertTrue($status->hasErrors());
        $this->assertSame('DB error', $status->getFirstErrorMessage());
        $this->assertSame('Something went wrong', $status->getFirstFriendlyMessage());
    }

    /**
     * Verify empty error/friendly message strings are not stored in the arrays.
     */
    public function testEmptyMessagesAreNotStored(): void
    {
        $status = new ActionStatus(false);

        $this->assertFalse($status->getStatus());
        $this->assertSame([], $status->getErrorMessages());
        $this->assertFalse($status->hasFriendlyMessages());
    }

    /**
     * Verify a KirbyRetrievalException can be stored and retrieved.
     */
    public function testExceptionStorage(): void
    {
        $exception = new KirbyRetrievalException('Not found');
        $status = new ActionStatus(false, 'Error', '', $exception);

        $this->assertSame($exception, $status->getException());
    }
}
