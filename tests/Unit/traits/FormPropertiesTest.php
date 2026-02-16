<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\traits;

use BSBI\WebBase\models\FeedbackForm;
use PHPUnit\Framework\TestCase;

/**
 * Tests the FormProperties trait via FeedbackForm (a concrete user of the trait).
 *
 * Covers CSRF token, Turnstile site key, submission success flag,
 * and inherited ErrorHandling availability.
 */
final class FormPropertiesTest extends TestCase
{
    /**
     * Create a FeedbackForm for testing FormProperties methods.
     *
     * @return FeedbackForm
     */
    private function createForm(): FeedbackForm
    {
        return new FeedbackForm();
    }

    /**
     * Verify CSRF token defaults to an empty string.
     */
    public function testCSRFTokenDefaultsToEmpty(): void
    {
        $form = $this->createForm();

        $this->assertSame('', $form->getCSRFToken());
    }

    /**
     * Verify CSRF token can be set and retrieved.
     */
    public function testCSRFTokenGetterSetter(): void
    {
        $form = $this->createForm();
        $form->setCSRFToken('secure-token-123');

        $this->assertSame('secure-token-123', $form->getCSRFToken());
    }

    /**
     * Verify submission successful defaults to false.
     */
    public function testSubmissionSuccessfulDefaultsToFalse(): void
    {
        $form = $this->createForm();

        $this->assertFalse($form->isSubmissionSuccessful());
    }

    /**
     * Verify submission successful flag can be toggled.
     */
    public function testSubmissionSuccessfulGetterSetter(): void
    {
        $form = $this->createForm();
        $form->setSubmissionSuccessful(true);

        $this->assertTrue($form->isSubmissionSuccessful());
    }

    /**
     * Verify Turnstile site key can be set and retrieved.
     */
    public function testTurnstileSiteKeyGetterSetter(): void
    {
        $form = $this->createForm();
        $form->setTurnstileSiteKey('0x4AAAA');

        $this->assertSame('0x4AAAA', $form->getTurnstileSiteKey());
    }

    /**
     * Verify ErrorHandling methods are available through FormProperties.
     */
    public function testErrorHandlingIsAvailableViaFormProperties(): void
    {
        $form = $this->createForm();

        $form->recordError('Validation failed');
        $this->assertTrue($form->hasErrors());
        $this->assertSame('Validation failed', $form->getFirstErrorMessage());
    }
}
