<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/**
 * Shared SEO fields (title, description, optionally the sharing image).
 */
final class SeoSection
{
    public static function make(?string $imageField = null, string $imageDirectory = 'seo'): Section
    {
        $fields = [
            TextInput::make('seo_title')
                ->label(__('admin.fields.seo_title'))
                ->helperText(__('admin.fields.seo_title_help'))
                ->maxLength(70),
            Textarea::make('seo_description')
                ->label(__('admin.fields.seo_description'))
                ->helperText(__('admin.fields.seo_description_help'))
                ->rows(3)
                ->maxLength(200),
        ];

        if ($imageField) {
            $fields[] = WebpUpload::make($imageField, $imageDirectory)
                ->label(__('admin.fields.og_image'))
                ->helperText(__('admin.fields.og_image_help'));
        }

        return Section::make(__('admin.sections.seo'))
            ->description(__('admin.sections.seo_help'))
            ->schema($fields)
            ->collapsible();
    }
}
