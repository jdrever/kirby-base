<?php

namespace BSBI\WebBase\interfaces;

/**
 * @template T
 */
interface ListHandler
{

    /**
     * @return T[]
     */
    public function getListItems(): array;
}