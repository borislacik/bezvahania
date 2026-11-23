<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Concerns\FillsTranslatableFields;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    use FillsTranslatableFields;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label(__('site.common.detail'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (Page $record): string => route(Page::KEYS[$record->key] ?? 'home'), shouldOpenInNewTab: true),
        ];
    }
}
