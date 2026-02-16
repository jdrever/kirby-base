<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\helpers\KirbyRetrievalException;
use BSBI\WebBase\models\ActionStatus;
use PHPUnit\Framework\TestCase;

final class ActionStatusTest extends TestCase
{
    public function testSuccessfulStatus(): void
    {
        $status = new ActionStatus(true);

        $this->assertTrue($status->getStatus());
        $this->assertTrue($status->didComplete());
        $this->assertFalse($status->hasErrors());
    }

    public function testFailedStatusWithMessages(): void
    {
        $status = new ActionStatus(false, 'DB error', 'Something went wrong');

        $this->assertFalse($status->getStatus());
        $this->assertTrue($status->hasErrors());
        $this->assertSame('DB error', $status->getFirstErrorMessage());
        $this->assertSame('Something went wrong', $status->getFirstFriendlyMessage());
    }

    public function testEmptyMessagesAreNotStored(): void
    {
        $status = new ActionStatus(false);

        $this->assertFalse($status->getStatus());
        $this->assertSame([], $status->getErrorMessages());
        $this->assertFalse($status->hasFriendlyMessages());
    }

    public function testExceptionStorage(): void
    {
        $exception = new KirbyRetrievalException('Not found');
        $status = new ActionStatus(false, 'Error', '', $exception);

        $this->assertSame($exception, $status->getException());
    }
}
