<?php

declare(strict_types=1);

namespace BSBI\WebBase\forms;

/**
 * Immutable result returned by a FormSubmissionHandler after processing a
 * validated form POST.
 *
 * Three outcomes are possible:
 *  - success:  form was processed; $message is shown to the user
 *  - error:    processing failed; $message explains why
 *  - redirect: the response should redirect to $redirectUrl (e.g. Stripe checkout)
 *
 * Use the static factories rather than the constructor:
 *
 *   FormSubmissionResult::success('Thank you, your submission has been received.')
 *   FormSubmissionResult::error('Payment could not be initialised. Please try again.')
 *   FormSubmissionResult::redirect($stripeCheckoutUrl)
 */
readonly class FormSubmissionResult
{
    private function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?string $redirectUrl,
    ) {
    }

    /**
     * The form was processed successfully. $message is displayed to the user.
     */
    public static function success(string $message = 'Thank you, your submission has been received.'): static
    {
        return new static(true, $message, null);
    }

    /**
     * Processing failed. $message explains what went wrong.
     */
    public static function error(string $message): static
    {
        return new static(false, $message, null);
    }

    /**
     * The browser should be redirected to $url (e.g. a Stripe checkout page).
     * The form infrastructure will call go($url) — the handler should not redirect itself.
     */
    public static function redirect(string $url): static
    {
        return new static(true, '', $url);
    }

    /**
     * Returns true if the framework should redirect the browser to $redirectUrl.
     */
    public function isRedirect(): bool
    {
        return $this->redirectUrl !== null;
    }
}
