<?php

namespace App\Filament\Resources\TeamMembers;

use App\Filament\Resources\TeamMembers\Pages\ManageTeamMembers;
use App\Filament\Support\Translatable;
use App\Filament\Support\WebpUpload;
use App\Models\TeamMember;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?int $navigationSort = 20;

    public static function getModelLabel(): string
    {
        return __('admin.resources.team_member.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.team_member.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.content');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label(__('admin.fields.name'))->required()->maxLength(120),
            TextInput::make('role')->label(__('admin.fields.role'))->required()->maxLength(160),
            Textarea::make('quote')->label(__('admin.fields.quote'))->rows(4)->columnSpanFull(),
            Textarea::make('bio')->label(__('admin.fields.bio'))->rows(5)->columnSpanFull(),
            WebpUpload::make('photo', 'team')->label(__('admin.fields.photo'))->columnSpanFull(),
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
                ImageColumn::make('photo')->label(__('admin.fields.photo'))->disk('public')->circular(),
                TextColumn::make('name')->label(__('admin.fields.name'))->searchable(),
                TextColumn::make('role')->label(__('admin.fields.role')),
                ToggleColumn::make('is_active')->label(__('admin.fields.is_active')),
            ])
            ->recordActions([
                EditAction::make()->mutateRecordDataUsing(fn (array $data, TeamMember $record): array => Translatable::fill($record, $data)),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTeamMembers::route('/')];
    }
}
