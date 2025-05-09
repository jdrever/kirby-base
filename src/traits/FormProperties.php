<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\models\WebPageLink;
use BSBI\WebBase\models\WebPageLinks;

trait FormProperties {

    private string $turnstileSiteKey;

    public function getTurnstileSiteKey(): string
    {
        return $this->turnstileSiteKey;
    }

    public function setTurnstileSiteKey(string $turnstileSiteKey): self
    {
        $this->turnstileSiteKey = $turnstileSiteKey;
        return $this;
    }
}

