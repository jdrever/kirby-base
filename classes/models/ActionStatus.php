<?php

namespace BSBI\WebBase\models;


use BSBI\WebBase\helpers\KirbyRetrievalException;
use BSBI\WebBase\traits\ErrorHandling;

/**
 * Class ActionStatus
 * Represents a simple action with status/error messages
 *
 * @package BSBI\Web
 */
class ActionStatus
{

    use ErrorHandling;
    private KirbyRetrievalException|null $exception;

    /**
     * @param bool $status
     * @param string $errorMessage
     * @param string $friendlyMessage
     * @param KirbyRetrievalException|null $exception
     */
    public function __construct(bool                    $status,
                                string                  $errorMessage = '',
                                string                  $friendlyMessage = '',
                                KirbyRetrievalException $exception = null)
    {
        $this->status = $status;
        $this->errorMessages[] = $errorMessage;
        $this->friendlyMessages[] = $friendlyMessage;
        $this->exception = $exception;
    }

    /**
     * @return KirbyRetrievalException
     */
    /**
     * @return KirbyRetrievalException
     */
    public function getException(): KirbyRetrievalException
    {
        return $this->exception;
    }

}
