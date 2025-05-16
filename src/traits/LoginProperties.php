<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\models\LoginDetails;

trait LoginProperties
{
    private LoginDetails $loginDetails;

    public function getLoginDetails(): LoginDetails
    {
        return $this->loginDetails;
    }

    public function setLoginDetails(LoginDetails $loginDetails): void
    {
        $this->loginDetails = $loginDetails;
    }
}