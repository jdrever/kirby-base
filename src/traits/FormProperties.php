<?php

namespace BSBI\WebBase\traits;


trait FormProperties {

    use ErrorHandling;

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

