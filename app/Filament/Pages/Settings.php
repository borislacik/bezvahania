<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Contact details, operator data and booking rules (stored in the settings table).
 *
 * @property-read Schema $form
 */
class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.settings.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin.settings.title');
    }

    public function mount(): void
    {
        $this->form->fill(Setting::allValues());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('admin.settings.contact'))
                    ->description(__('admin.settings.sender_note', ['address' => (string) config('mail.from.address')]))
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact_phone')->label(__('admin.settings.contact_phone'))->tel()->required()->maxLength(40),
                        TextInput::make('contact_email')->label(__('admin.settings.contact_email'))->email()->required()->maxLength(190),
                        TextInput::make('notification_email')->label(__('admin.settings.notification_email'))->email()->required()->maxLength(190)->columnSpanFull(),
                        TextInput::make('social_facebook')->label(__('admin.settings.social_facebook'))->helperText(__('admin.settings.social_facebook_help'))->url()->maxLength(255)->columnSpanFull(),
                    ]),
                Section::make(__('admin.settings.operator'))->columns(2)->schema([
                    TextInput::make('operator_name')->label(__('admin.settings.operator_name'))->maxLength(160),
                    TextInput::make('operator_ico')->label(__('admin.settings.operator_ico'))->maxLength(20),
                    TextInput::make('operator_dic')->label(__('admin.settings.operator_dic'))->maxLength(20),
                    TextInput::make('operator_vat_note')->label(__('admin.settings.operator_vat_note'))->helperText(__('admin.settings.operator_vat_note_help'))->maxLength(120),
                    TextInput::make('operator_address')->label(__('admin.settings.operator_address'))->maxLength(255)->columnSpanFull(),
                ]),
                Section::make(__('admin.settings.booking'))->schema([
                    TextInput::make('booking_lead_days')->label(__('admin.settings.booking_lead_days'))->numeric()->minValue(0)->maxValue(30)->required(),
                    TextInput::make('bundle_price')->label(__('admin.settings.bundle_price'))->helperText(__('admin.settings.bundle_price_help'))->numeric()->minValue(0)->suffix('€')->required(),
                ]),
            ]);
    }

    public function save(): void
    {
        /** @var array<string, string|int|null> $values */
        $values = $this->form->getState();

        Setting::put(array_intersect_key($values, Setting::DEFAULTS));

        Notification::make()->success()->title(__('admin.settings.saved'))->send();
    }
}
