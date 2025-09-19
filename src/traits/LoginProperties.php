<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\models\LoginDetails;

/**
 *
 */
trait LoginProperties
{
    private LoginDetails $loginDetails;

    /**
     * @return LoginDetails
     */
    public function getLoginDetails(): LoginDetails
    {
        return $this->loginDetails;
    }

    /**
     * @param LoginDetails $loginDetails
     * @return void
     */
    public function setLoginDetails(LoginDetails $loginDetails): void
    {
        $this->loginDetails = $loginDetails;
    }
}