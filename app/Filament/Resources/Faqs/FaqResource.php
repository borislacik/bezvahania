<?php

namespace App\Filament\Resources\Faqs;

use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Filament\Support\Translatable;
use App\Models\Faq;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return __('admin.resources.faq.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.faq.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.content');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('question')->label(__('admin.fields.question'))->required()->maxLength(255)->columnSpanFull(),
            Textarea::make('answer')->label(__('admin.fields.answer'))->required()->rows(5)->columnSpanFull(),
            TextInput::make('sort_order')->label(__('admin.fields.sort_order'))->numeric()->default(0)->required(),
            Toggle::make('is_active')->label(__('admin.fields.is_active'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('question')->label(__('admin.fields.question'))->wrap()->searchable(),
                ToggleColumn::make('is_active')->label(__('admin.fields.is_active')),
            ])
            ->recordActions([
                EditAction::make()->mutateRecordDataUsing(fn (array $data, Faq $record): array => Translatable::fill($record, $data)),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageFaqs::route('/')];
    }
}
