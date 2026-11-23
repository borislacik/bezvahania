<?php

namespace App\Filament\Support;

use App\Support\WebpImage;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Image upload for the admin: accepts JPEG/PNG/WebP/GIF/AVIF and always stores WebP
 * (docs/decisions.md – all images on the website are WebP).
 */
final class WebpUpload
{
    public static function make(string $name, string $directory): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'])
            ->maxSize(15 * 1024)
            ->imagePreviewHeight('200')
            ->helperText(__('admin.fields.image_help'))
            ->saveUploadedFileUsing(
                fn (TemporaryUploadedFile $file): string => WebpImage::store($file->getRealPath(), $directory),
            );
    }
}
