<?php

namespace BSBI\WebBase\traits;

use BSBI\WebBase\models\Image;

/**
 * Trait ImageHandling
 * To add an image to a model
 *
 * @package BSBI\traits
 */
trait OptionsHandling
{

    /**
     * @param string $value
     * @param string $display
     * @return string[]
     */
    protected function createOption(string $value, string $display): array
    {
        return [
            'value' => $value,
            'display' => $display,
        ];

    }

    /**
     * @param array $options
     * @return array
     * @noinspection PhpUnused
     */
    protected function getSimpleSelectOptions(array $options): array
    {
        $selectOptions = [];
        $selectOptions[] = $this->createOption('', 'Any');
        foreach ($options as $option) {
            $selectOptions[] = $this->createOption($option, $option);
        }
        return $selectOptions;
    }

}
