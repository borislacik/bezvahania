<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Support\SeoSection;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * SEO metadata and texts of the fixed public pages. Pages cannot be created or deleted.
 */
class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 50;

    public static function getModelLabel(): string
    {
        return __('admin.resources.page.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.page.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.content');
    }

    /**
     * Only pages that still exist (keys in Page::KEYS); rows of former pages stay in the database but are hidden.
     *
     * @return Builder<Page>
     */
    public static function getEloquentQuery(): Builder
    {
        return Page::query()->whereIn('key', array_keys(Page::KEYS));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make(__('admin.sections.content'))->schema([
                TextInput::make('title')->label(__('admin.fields.title'))->required()->maxLength(160),
                RichEditor::make('content')
                    ->label(__('admin.fields.content'))
                    ->helperText(__('admin.fields.content_help'))
                    ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['undo', 'redo']])
                    ->visible(fn (?Page $record): bool => in_array($record?->key, ['privacy', 'terms', 'cancellation'], true)),
            ]),
            SeoSection::make('og_image', 'seo')->collapsed(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('title')->label(__('admin.fields.key'))->weight('bold'),
                TextColumn::make('seo_title')->label(__('admin.fields.seo_title'))->limit(60),
                TextColumn::make('seo_description')->label(__('admin.fields.seo_description'))->limit(60)->toggleable(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
