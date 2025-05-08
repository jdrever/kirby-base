<?php

namespace BSBI\WebBase\traits;


use BSBI\WebBase\models\PrevNextPageNavigation;

trait PrevNextPageProperties {

    private PrevNextPageNavigation $prevNextPageNavigation;

    public function getPrevNextPageNavigation(): PrevNextPageNavigation
    {
        return $this->prevNextPageNavigation;
    }

    public function setPrevNextPageNavigation(PrevNextPageNavigation $prevNextPageNavigation): self
    {
        $this->prevNextPageNavigation = $prevNextPageNavigation;
        return $this;
    }

}