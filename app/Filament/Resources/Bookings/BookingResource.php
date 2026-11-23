<?php

namespace App\Filament\Resources\Bookings;

use App\Enums\BookingStatus;
use App\Enums\TimeSlot;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Pages\ViewBooking;
use App\Models\Booking;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getModelLabel(): string
    {
        return __('admin.resources.booking.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.booking.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.bookings');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Booking::query()->upcoming()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.sections.booking'))->columns(2)->schema([
                Select::make('course_id')
                    ->label(__('admin.fields.course'))
                    ->options(fn (): array => Course::query()->orderBy('sort_order')->get()->pluck('name', 'id')->all())
                    ->required()
                    ->disabledOn('edit')
                    ->columnSpanFull(),
                DatePicker::make('date')
                    ->label(__('admin.fields.date'))
                    ->native(false)
                    ->displayFormat('j. n. Y')
                    ->firstDayOfWeek(1)
                    ->minDate(today())
                    ->required()
                    ->disabledOn('edit'),
                Select::make('time_slot')
                    ->label(__('admin.fields.time_slot'))
                    ->options(TimeSlot::class)
                    ->required()
                    ->disabledOn('edit'),
                Select::make('participants')
                    ->label(__('admin.fields.participants'))
                    ->options(['1' => '1', '2' => '2', '3' => '3', '4' => '4'])
                    ->default(4)
                    ->required(),
            ]),
            Section::make(__('admin.sections.customer'))->columns(2)->schema([
                TextInput::make('contact_name')->label(__('admin.fields.contact_name'))->required()->maxLength(120),
                TextInput::make('phone')->label(__('admin.fields.phone'))->tel()->required()->maxLength(40),
                TextInput::make('email')->label(__('admin.fields.email'))->email()->required()->maxLength(190),
                TextInput::make('address')->label(__('admin.fields.address'))->required()->maxLength(255),
                Textarea::make('note')->label(__('admin.fields.note'))->rows(3)->columnSpanFull(),
                Toggle::make('notify')
                    ->label(__('admin.fields.notify_customer'))
                    ->default(true)
                    ->dehydrated()
                    ->visibleOn('create'),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.sections.booking'))->columns(3)->schema([
                TextEntry::make('reference')->label(__('admin.fields.reference'))->weight('bold')->copyable(),
                TextEntry::make('status')->label(__('admin.fields.status'))->badge(),
                TextEntry::make('course.name')->label(__('admin.fields.course')),
                TextEntry::make('date')->label(__('admin.fields.date'))->date('l j. n. Y'),
                TextEntry::make('time_slot')->label(__('admin.fields.time_slot')),
                TextEntry::make('participants')->label(__('admin.fields.participants')),
                TextEntry::make('price')->label(__('admin.fields.price'))->money('EUR', locale: 'sk'),
                TextEntry::make('created_at')->label(__('admin.fields.created_at'))->dateTime('j. n. Y H:i'),
                TextEntry::make('cancellation_reason')->label(__('admin.fields.cancellation_reason'))->visible(fn (Booking $record): bool => $record->status === BookingStatus::Cancelled),
            ]),
            Section::make(__('admin.sections.customer'))->columns(2)->schema([
                TextEntry::make('contact_name')->label(__('admin.fields.contact_name')),
                TextEntry::make('phone')->label(__('admin.fields.phone'))->url(fn (Booking $record): string => 'tel:'.preg_replace('/\s+/', '', $record->phone)),
                TextEntry::make('email')->label(__('admin.fields.email'))->url(fn (Booking $record): string => 'mailto:'.$record->email),
                TextEntry::make('address')->label(__('admin.fields.address')),
                TextEntry::make('note')->label(__('admin.fields.note'))->placeholder('—')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date')
            ->columns([
                TextColumn::make('date')->label(__('admin.fields.date'))->date('D j. n. Y')->sortable(),
                TextColumn::make('time_slot')->label(__('admin.fields.time_slot')),
                TextColumn::make('course.name')->label(__('admin.fields.course'))->wrap(),
                TextColumn::make('contact_name')->label(__('admin.fields.contact_name'))->searchable(),
                TextColumn::make('phone')->label(__('admin.fields.phone'))->searchable()->toggleable(),
                TextColumn::make('participants')->label(__('admin.fields.participants'))->alignCenter(),
                TextColumn::make('status')->label(__('admin.fields.status'))->badge(),
                TextColumn::make('reference')->label(__('admin.fields.reference'))->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label(__('admin.fields.created_at'))->dateTime('j. n. Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('upcoming')
                    ->label(__('admin.filters.upcoming'))
                    ->default()
                    ->query(fn (Builder $query): Builder => $query->whereDate('date', '>=', today())),
                SelectFilter::make('status')->label(__('admin.fields.status'))->options(BookingStatus::class),
                SelectFilter::make('course_id')->label(__('admin.fields.course'))->relationship('course', 'slug')
                    ->getOptionLabelFromRecordUsing(fn (Course $record): string => $record->name),
                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('from')->label(__('admin.filters.date_from')),
                        DatePicker::make('until')->label(__('admin.filters.date_until')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('date', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    EditAction::make(),
                    ...BookingActions::all(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'view' => ViewBooking::route('/{record}'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}
