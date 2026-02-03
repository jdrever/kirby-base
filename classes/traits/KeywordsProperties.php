<?php

namespace BSBI\WebBase\traits;


trait KeywordsProperties
{

    private string $keywords = '';

    public function hasKeywords(): bool {
        return !empty($this->keywords);
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     * @return $this
     */
    public function setKeywords(string $keywords): self
    {
        $this->keywords = $keywords;
        return $this;
    }


}
