<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Concerns\FillsTranslatableFields;
use App\Filament\Resources\Courses\CourseResource;
use App\Models\Course;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    use FillsTranslatableFields;

    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label(__('site.common.detail'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (Course $record): string => route('workshops.show', $record), shouldOpenInNewTab: true),
            // Workshops with bookings cannot be deleted (foreign key); hide them instead.
            DeleteAction::make()->visible(fn (Course $record): bool => $record->bookings()->doesntExist()),
        ];
    }
}
