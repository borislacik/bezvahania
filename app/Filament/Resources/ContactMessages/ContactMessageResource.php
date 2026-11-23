<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('admin.resources.contact_message.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.contact_message.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.bookings');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ContactMessage::query()->whereNull('handled_at')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextEntry::make('name')->label(__('admin.fields.contact_name')),
            TextEntry::make('created_at')->label(__('admin.fields.received_at'))->dateTime('j. n. Y H:i'),
            TextEntry::make('email')->label(__('admin.fields.email'))->url(fn (ContactMessage $record): string => 'mailto:'.$record->email),
            TextEntry::make('phone')->label(__('admin.fields.phone'))->placeholder('—'),
            TextEntry::make('message')->label(__('admin.fields.message'))->columnSpanFull()->prose(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('handled_at')->label(__('admin.fields.handled_at'))->boolean()->getStateUsing(fn (ContactMessage $record): bool => $record->handled_at !== null),
                TextColumn::make('name')->label(__('admin.fields.contact_name'))->searchable(),
                TextColumn::make('email')->label(__('admin.fields.email'))->searchable(),
                TextColumn::make('message')->label(__('admin.fields.message'))->limit(60),
                TextColumn::make('created_at')->label(__('admin.fields.received_at'))->dateTime('j. n. Y H:i')->sortable(),
            ])
            ->filters([
                Filter::make('unhandled')->label(__('admin.filters.unhandled'))->query(fn (Builder $query): Builder => $query->whereNull('handled_at')),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('handled')
                    ->label(__('admin.actions.mark_handled'))
                    ->icon('heroicon-o-check')
                    ->visible(fn (ContactMessage $record): bool => $record->handled_at === null)
                    ->action(function (ContactMessage $record): void {
                        $record->update(['handled_at' => now()]);
                        Notification::make()->success()->title(__('admin.actions.marked_handled'))->send();
                    }),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageContactMessages::route('/')];
    }
}
