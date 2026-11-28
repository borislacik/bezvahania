<?php

namespace App\Filament\Resources\Courses;

use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Filament\Support\SeoSection;
use App\Filament\Support\WebpUpload;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return __('admin.resources.course.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.course.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.content');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->columnSpanFull()->schema([
                Section::make(__('admin.sections.basic'))->columnSpan(2)->columns(2)->schema([
                    TextInput::make('name')
                        ->label(__('admin.fields.name'))
                        ->required()
                        ->maxLength(160)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                            if ($operation === 'create') {
                                $set('slug', Str::slug((string) $state));
                            }
                        })
                        ->columnSpanFull(),
                    TextInput::make('slug')
                        ->label(__('admin.fields.slug'))
                        ->helperText(__('admin.fields.slug_help'))
                        ->required()
                        ->alphaDash()
                        ->maxLength(160)
                        ->unique(ignoreRecord: true),
                    TextInput::make('badge')->label(__('admin.fields.badge'))->helperText(__('admin.fields.badge_help'))->maxLength(40),
                    TextInput::make('price')->label(__('admin.fields.price'))->numeric()->minValue(0)->suffix('€')->required(),
                    TextInput::make('duration_label')->label(__('admin.fields.duration_label'))->required()->maxLength(60)->placeholder('cca 3 hodiny'),
                    TextInput::make('equipment')->label(__('admin.fields.equipment'))->maxLength(160)->columnSpanFull(),
                    Textarea::make('short_description')->label(__('admin.fields.short_description'))->required()->rows(3)->maxLength(400)->columnSpanFull(),
                    RichEditor::make('description')
                        ->label(__('admin.fields.description'))
                        ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['undo', 'redo']])
                        ->columnSpanFull(),
                ]),
                Grid::make(1)->columnSpan(1)->schema([
                    Section::make(__('admin.fields.image'))->schema([
                        WebpUpload::make('image', 'courses')->hiddenLabel(),
                    ]),
                    Section::make(__('admin.sections.state'))->schema([
                        Toggle::make('is_active')->label(__('admin.fields.is_active'))->default(true),
                        Toggle::make('is_featured')->label(__('admin.fields.is_featured')),
                        Toggle::make('is_bundled')->label(__('admin.fields.is_bundled'))->helperText(__('admin.fields.is_bundled_help')),
                        TextInput::make('sort_order')->label(__('admin.fields.sort_order'))->numeric()->default(0)->required(),
                    ]),
                    SeoSection::make(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image')->label(__('admin.fields.image'))->disk('public'),
                TextColumn::make('name')->label(__('admin.fields.name'))->searchable()->weight('bold'),
                TextColumn::make('price')->label(__('admin.fields.price'))->money('EUR', locale: 'sk'),
                TextColumn::make('duration_label')->label(__('admin.fields.duration_label')),
                ToggleColumn::make('is_active')->label(__('admin.fields.is_active')),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }
}
