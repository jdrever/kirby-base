<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\FeedbackForm;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the FeedbackForm model.
 *
 * Covers field value/alert defaults, getters and setters,
 * FormProperties trait integration (CSRF, submission status),
 * and fluent interface return values.
 */
final class FeedbackFormTest extends TestCase
{
    /**
     * Create a FeedbackForm instance for testing.
     *
     * @return FeedbackForm
     */
    private function createForm(): FeedbackForm
    {
        return new FeedbackForm();
    }

    /**
     * Verify all value and alert fields default to empty strings.
     */
    public function testFieldsDefaultToEmpty(): void
    {
        $form = $this->createForm();

        $this->assertSame('', $form->getNameValue());
        $this->assertSame('', $form->getEmailValue());
        $this->assertSame('', $form->getFeedbackValue());
        $this->assertSame('', $form->getNameAlert());
        $this->assertSame('', $form->getEmailAlert());
        $this->assertSame('', $form->getFeedbackAlert());
    }

    /**
     * Verify name, email and feedback values can be set and retrieved.
     */
    public function testValueGettersSetters(): void
    {
        $form = $this->createForm();

        $form->setNameValue('Alice');
        $form->setEmailValue('alice@example.com');
        $form->setFeedbackValue('Great site!');

        $this->assertSame('Alice', $form->getNameValue());
        $this->assertSame('alice@example.com', $form->getEmailValue());
        $this->assertSame('Great site!', $form->getFeedbackValue());
    }

    /**
     * Verify name, email and feedback alert messages can be set and retrieved.
     */
    public function testAlertGettersSetters(): void
    {
        $form = $this->createForm();

        $form->setNameAlert('Name is required');
        $form->setEmailAlert('Invalid email');
        $form->setFeedbackAlert('Feedback too short');

        $this->assertSame('Name is required', $form->getNameAlert());
        $this->assertSame('Invalid email', $form->getEmailAlert());
        $this->assertSame('Feedback too short', $form->getFeedbackAlert());
    }

    /**
     * Verify FormProperties trait methods (CSRF token, submission status) are accessible.
     */
    public function testFormPropertiesAreAccessible(): void
    {
        $form = $this->createForm();

        $form->setCSRFToken('token123');
        $this->assertSame('token123', $form->getCSRFToken());

        $form->setSubmissionSuccessful(true);
        $this->assertTrue($form->isSubmissionSuccessful());
    }

    /**
     * Verify all setters return the same instance for fluent chaining.
     */
    public function testSettersReturnSelf(): void
    {
        $form = $this->createForm();

        $this->assertSame($form, $form->setNameValue('x'));
        $this->assertSame($form, $form->setEmailValue('x'));
        $this->assertSame($form, $form->setFeedbackValue('x'));
        $this->assertSame($form, $form->setNameAlert('x'));
        $this->assertSame($form, $form->setEmailAlert('x'));
        $this->assertSame($form, $form->setFeedbackAlert('x'));
    }
}
