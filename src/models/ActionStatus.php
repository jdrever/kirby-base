<?php

namespace BSBI\WebBase\models;


/**
 * Class ActionStatus
 * Represents a simple action with status/error messages
 *
 * @package BSBI\Web
 */
class ActionStatus extends BaseModel
{

    /**
     * @param bool $status
     * @param string $errorMessage
     * @param string $friendlyMessage
     */
    public function __construct(bool $status, string $errorMessage = '', string $friendlyMessage = '')
    {
        $this->status = $status;
        $this->errorMessages[] = $errorMessage;
        $this->friendlyMessages[] = $friendlyMessage;
    }


}
