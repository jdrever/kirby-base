<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\models\FeedbackForm;
use PHPUnit\Framework\TestCase;

/**
 * Tests the FormProperties trait via FeedbackForm (a concrete user of the trait).
 */
final class FormPropertiesTest extends TestCase
{
    private function createForm(): FeedbackForm
    {
        return new FeedbackForm();
    }

    public function testCSRFTokenDefaultsToEmpty(): void
    {
        $form = $this->createForm();

        $this->assertSame('', $form->getCSRFToken());
    }

    public function testCSRFTokenGetterSetter(): void
    {
        $form = $this->createForm();
        $form->setCSRFToken('secure-token-123');

        $this->assertSame('secure-token-123', $form->getCSRFToken());
    }

    public function testSubmissionSuccessfulDefaultsToFalse(): void
    {
        $form = $this->createForm();

        $this->assertFalse($form->isSubmissionSuccessful());
    }

    public function testSubmissionSuccessfulGetterSetter(): void
    {
        $form = $this->createForm();
        $form->setSubmissionSuccessful(true);

        $this->assertTrue($form->isSubmissionSuccessful());
    }

    public function testTurnstileSiteKeyGetterSetter(): void
    {
        $form = $this->createForm();
        $form->setTurnstileSiteKey('0x4AAAA');

        $this->assertSame('0x4AAAA', $form->getTurnstileSiteKey());
    }

    public function testErrorHandlingIsAvailableViaFormProperties(): void
    {
        $form = $this->createForm();

        $form->recordError('Validation failed');
        $this->assertTrue($form->hasErrors());
        $this->assertSame('Validation failed', $form->getFirstErrorMessage());
    }
}
