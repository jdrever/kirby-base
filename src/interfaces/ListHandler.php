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
     * @noinspection PhpUnused
     */
    public function getListItems(): array;

}