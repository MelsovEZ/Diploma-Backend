<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;

class ImageCompressor
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function compress(UploadedFile $file, int $width = 800): string
    {
        $image = $this->manager->read($file->getPathname());

        // Изменение размера
        $image->scale(width: $width);

        // Возвращаем сжатое изображение как строку
        return (string) $image->toJpeg(); // Можно заменить на toPng() и т.п.
    }
}
