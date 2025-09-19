<?php

namespace BSBI\WebBase\traits;


use BSBI\WebBase\models\PrevNextPageNavigation;

/**
 *
 */
trait PrevNextPageProperties {

    private PrevNextPageNavigation $prevNextPageNavigation;

    /**
     * @return PrevNextPageNavigation
     */
    public function getPrevNextPageNavigation(): PrevNextPageNavigation
    {
        return $this->prevNextPageNavigation;
    }

    /**
     * @param PrevNextPageNavigation $prevNextPageNavigation
     * @return PrevNextPageProperties
     */
    public function setPrevNextPageNavigation(PrevNextPageNavigation $prevNextPageNavigation): self
    {
        $this->prevNextPageNavigation = $prevNextPageNavigation;
        return $this;
    }

}