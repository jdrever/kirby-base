<?php

namespace BSBI\WebBase\traits;


trait LimitProperties
{

    private int $limit = 0;

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self {
        $this->limit = $limit;
        return $this;
    }

}
