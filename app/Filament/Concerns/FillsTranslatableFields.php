<?php

namespace App\Filament\Concerns;

use App\Filament\Support\Translatable;

/**
 * For EditRecord pages of models using spatie/laravel-translatable.
 */
trait FillsTranslatableFields
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return Translatable::fill($this->getRecord(), $data);
    }
}
