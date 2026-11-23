<?php

namespace App\Filament\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Bridges spatie/laravel-translatable and Filament forms: the form edits the value for the
 * current locale (Slovak), saving through the model stores it as that locale's translation.
 * When more languages are added, the forms get a locale switcher (docs/features/admin.md).
 */
final class Translatable
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function fill(Model $record, array $data): array
    {
        if (! method_exists($record, 'getTranslatableAttributes') || ! method_exists($record, 'getTranslation')) {
            return $data;
        }

        foreach ($record->getTranslatableAttributes() as $attribute) {
            $data[$attribute] = $record->getTranslation($attribute, app()->getLocale(), false) ?: null;
        }

        return $data;
    }
}
