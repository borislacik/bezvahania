<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Resources\Pages\EditRecord;

/**
 * Only contact details, the number of participants and the note can be edited.
 * A different date or workshop means cancelling and creating a new booking.
 */
class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return array_intersect_key($data, array_flip(['contact_name', 'phone', 'email', 'address', 'participants', 'note']));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
