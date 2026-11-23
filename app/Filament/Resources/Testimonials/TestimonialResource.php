<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Filament\Support\Translatable;
use App\Models\Testimonial;
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

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?int $navigationSort = 40;

    public static function getModelLabel(): string
    {
        return __('admin.resources.testimonial.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.testimonial.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.content');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('author')->label(__('admin.fields.author'))->required()->maxLength(120),
            TextInput::make('rating')->label(__('admin.fields.rating'))->numeric()->minValue(1)->maxValue(5)->default(5)->required(),
            Textarea::make('text')->label(__('admin.fields.text'))->required()->rows(4)->columnSpanFull(),
            TextInput::make('context')->label(__('admin.fields.context'))->helperText(__('admin.fields.context_help'))->maxLength(160)->columnSpanFull(),
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
                TextColumn::make('author')->label(__('admin.fields.author'))->searchable(),
                TextColumn::make('text')->label(__('admin.fields.text'))->limit(80)->wrap(),
                ToggleColumn::make('is_active')->label(__('admin.fields.is_active')),
            ])
            ->recordActions([
                EditAction::make()->mutateRecordDataUsing(fn (array $data, Testimonial $record): array => Translatable::fill($record, $data)),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTestimonials::route('/')];
    }
}
