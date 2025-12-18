<?php

namespace BSBI\WebBase\filemodels;
use Kirby\Cms\File;

class FileArchiveItem extends File {
    public function url(array $options = null): string {
        $altSlug = $this->content()->get('permanentUrl');

        if ($altSlug->isNotEmpty()) {
            return url('files/' . $altSlug);
        }

        return parent::url($options);
    }
}
