<?php

declare(strict_types=1);

namespace BSBI\WebBase\forms;

use Kirby\Cms\Page;

/**
 * Strategy interface for custom form submission behaviour.
 *
 * Implement this interface to replace the default action of saving a
 * form_submission Kirby child page.  Associate the handler with a form
 * definition by overriding BaseFormDefinition::getSubmissionHandler().
 *
 * The framework validates the CSRF token before calling handle().  The
 * handler receives the validated POST data and returns a FormSubmissionResult.
 * It should NOT redirect directly — return FormSubmissionResult::redirect($url)
 * and the framework will call go() for you.
 *
 * Example:
 *
 *   class StripeCheckoutHandler implements FormSubmissionHandler
 *   {
 *       public function handle(Page $page, array $postData): FormSubmissionResult
 *       {
 *           $session = $this->stripe->checkout->sessions->create([...]);
 *           return FormSubmissionResult::redirect($session->url);
 *       }
 *   }
 */
interface FormSubmissionHandler
{
    /**
     * Processes a validated form POST and returns the outcome.
     *
     * @param Page  $page     The Kirby page the form lives on
     * @param array $postData The validated POST data (CSRF already verified by the framework)
     * @return FormSubmissionResult
     */
    public function handle(Page $page, array $postData): FormSubmissionResult;
}
