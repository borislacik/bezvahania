<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Actions\Bookings\CreateBooking as CreateBookingAction;
use App\Exceptions\SlotUnavailableException;
use App\Filament\Resources\Bookings\BookingResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

/**
 * Bookings taken by phone. The admin may book outside the public window,
 * but never a slot that is already taken.
 */
class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(CreateBookingAction::class)->handle([
                'course_id' => (int) $data['course_id'],
                'date' => (string) $data['date'],
                'time_slot' => $data['time_slot'] instanceof \BackedEnum ? (string) $data['time_slot']->value : (string) $data['time_slot'],
                'contact_name' => (string) $data['contact_name'],
                'email' => (string) $data['email'],
                'phone' => (string) $data['phone'],
                'address' => (string) $data['address'],
                'participants' => (int) $data['participants'],
                'note' => $data['note'] ?? null,
            ], checkWindow: false, notify: (bool) ($data['notify'] ?? true));
        } catch (SlotUnavailableException $exception) {
            Notification::make()->danger()->title(__('admin.actions.slot_unavailable'))->body($exception->getMessage())->send();

            throw new Halt;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
