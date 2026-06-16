<?php

declare(strict_types=1);

namespace BSBI\WebBase\helpers;

use RuntimeException;

/**
 * Handles server-side image format conversion.
 */
class ImageConversionHelper
{
    /**
     * Converts a BMP file to PNG and saves it to a temporary file.
     *
     * Tries Imagick first (supports all BMP variants including RLE-compressed),
     * then falls back to GD's imagecreatefrombmp() (PHP 7.2+, uncompressed BMP only).
     *
     * @param string $sourcePath Absolute path to the source BMP file
     * @return string Absolute path to the converted PNG temp file (caller must delete)
     * @throws RuntimeException If neither Imagick nor GD can convert the file
     */
    public static function convertBmpToPng(string $sourcePath): string
    {
        $tmpPath = sys_get_temp_dir() . '/' . uniqid('bmp_convert_', true) . '.png';

        if (class_exists('Imagick')) {
            try {
                $imagick = new \Imagick($sourcePath);
                $imagick->setImageFormat('png');
                $imagick->writeImage($tmpPath);
                $imagick->clear();
                return $tmpPath;
            } catch (\Throwable) {
                // Fall through to GD
            }
        }

        if (function_exists('imagecreatefrombmp')) {
            $image = imagecreatefrombmp($sourcePath);

            if ($image === false) {
                throw new RuntimeException('GD could not read BMP file: ' . $sourcePath);
            }

            $written = imagepng($image, $tmpPath, 9);
            imagedestroy($image);

            if ($written === false) {
                throw new RuntimeException('GD could not write PNG file: ' . $tmpPath);
            }

            return $tmpPath;
        }

        throw new RuntimeException(
            'No image library available (Imagick or GD with BMP support) to convert BMP to PNG'
        );
    }

    /**
     * Returns true if the server has a library capable of converting BMP files.
     */
    public static function canConvertBmp(): bool
    {
        return class_exists('Imagick') || function_exists('imagecreatefrombmp');
    }
}
