<?php

declare(strict_types=1);

namespace BSBI\WebBase\Tests\Unit\models;

use BSBI\WebBase\models\FeedbackForm;
use PHPUnit\Framework\TestCase;

final class FeedbackFormTest extends TestCase
{
    private function createForm(): FeedbackForm
    {
        return new FeedbackForm();
    }

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

    public function testFormPropertiesAreAccessible(): void
    {
        $form = $this->createForm();

        $form->setCSRFToken('token123');
        $this->assertSame('token123', $form->getCSRFToken());

        $form->setSubmissionSuccessful(true);
        $this->assertTrue($form->isSubmissionSuccessful());
    }

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
