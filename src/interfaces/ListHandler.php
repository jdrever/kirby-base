<?php

namespace BSBI\WebBase\interfaces;

/**
 * @deprecated
 * @template T
 */
interface ListHandler
{

    /**
     * @return T[]
     */
    public function getListItems(): array;

}