<?php

namespace App\Filament\Resources\Bookings;

use App\Actions\Bookings\CancelBooking;
use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

/**
 * Status actions shared by the bookings table and the booking detail page.
 */
final class BookingActions
{
    /**
     * @return list<Action>
     */
    public static function all(): array
    {
        return [self::complete(), self::noShow(), self::cancel()];
    }

    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label(__('admin.actions.cancel'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Booking $record): bool => $record->isCancellable())
            ->requiresConfirmation()
            ->modalHeading(fn (Booking $record): string => __('admin.actions.cancel_heading', ['reference' => $record->reference]))
            ->modalDescription(__('admin.actions.cancel_description'))
            ->schema([
                Textarea::make('reason')->label(__('admin.fields.cancellation_reason'))->rows(3)->maxLength(1000),
            ])
            ->action(function (Booking $record, array $data): void {
                $cancelBooking = app(CancelBooking::class);
                $repriced = $cancelBooking->dependentBookings($record)->pluck('reference');

                $cancelBooking->handle($record, $data['reason'] ?? null);

                Notification::make()->success()->title(__('admin.actions.cancelled'))
                    ->body($repriced->isNotEmpty() ? __('admin.actions.cancelled_repriced', ['references' => $repriced->implode(', ')]) : null)
                    ->send();
            });
    }

    public static function complete(): Action
    {
        return Action::make('complete')
            ->label(__('admin.actions.complete'))
            ->icon('heroicon-o-check-badge')
            ->color('info')
            ->visible(fn (Booking $record): bool => $record->status === BookingStatus::Confirmed && ! $record->date->isFuture())
            ->action(function (Booking $record): void {
                $record->update(['status' => BookingStatus::Completed]);
                Notification::make()->success()->title(__('admin.actions.completed'))->send();
            });
    }

    public static function noShow(): Action
    {
        return Action::make('no_show')
            ->label(__('admin.actions.no_show'))
            ->icon('heroicon-o-no-symbol')
            ->color('warning')
            ->visible(fn (Booking $record): bool => $record->status === BookingStatus::Confirmed && ! $record->date->isFuture())
            ->requiresConfirmation()
            ->action(function (Booking $record): void {
                $record->update(['status' => BookingStatus::NoShow]);
                Notification::make()->success()->title(__('admin.actions.no_show_done'))->send();
            });
    }
}
