<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label(__('admin.actions.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (): StreamedResponse => $this->exportCsv()),
            CreateAction::make(),
        ];
    }

    /**
     * Exports the currently filtered bookings (UTF-8 with BOM and semicolons, so Excel opens it correctly).
     */
    public function exportCsv(): StreamedResponse
    {
        $bookings = $this->getFilteredSortedTableQuery()?->with('course')->get() ?? collect();

        return response()->streamDownload(function () use ($bookings): void {
            $out = fopen('php://output', 'w');

            if ($out === false) {
                return;
            }

            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                __('admin.fields.reference'), __('admin.fields.date'), __('admin.fields.time_slot'), __('admin.fields.course'),
                __('admin.fields.status'), __('admin.fields.contact_name'), __('admin.fields.phone'), __('admin.fields.email'),
                __('admin.fields.address'), __('admin.fields.billing_address'), __('admin.fields.participants'), __('admin.fields.price'), __('admin.fields.note'),
            ], ';', escape: '');

            /** @var Booking $booking */
            foreach ($bookings as $booking) {
                fputcsv($out, [
                    $booking->reference,
                    $booking->date->format('j. n. Y'),
                    $booking->time_slot->label(),
                    $booking->course->name,
                    $booking->status->label(),
                    $booking->contact_name,
                    $booking->phone,
                    $booking->email,
                    $booking->address,
                    $booking->billing_address,
                    $booking->participants,
                    number_format((float) $booking->price, 2, ',', ''),
                    $booking->note,
                ], ';', escape: '');
            }

            fclose($out);
        }, __('admin.export.filename', ['date' => now()->format('Y-m-d')]), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
