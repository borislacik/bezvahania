<?php

namespace App\Http\Controllers;

use App\Actions\Bookings\CreateBooking;
use App\Enums\TimeSlot;
use App\Exceptions\SlotUnavailableException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Course;
use App\Models\Page;
use App\Support\Availability;
use App\Support\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class BookingController extends Controller
{
    public function create(Request $request): View
    {
        $courses = Course::query()->active()->get();
        $selectedCourse = $courses->firstWhere('slug', $request->query('workshop')) ?? $courses->firstWhere('is_featured', true) ?? $courses->first();

        return view('pages.booking.create', [
            'seo' => Seo::forPage(Page::forKey('booking')),
            'months' => (new Availability)->months(),
            'courses' => $courses,
            'selectedCourse' => $selectedCourse,
            'slots' => TimeSlot::cases(),
        ]);
    }

    public function store(StoreBookingRequest $request, CreateBooking $createBooking): RedirectResponse
    {
        try {
            $booking = $createBooking->handle($request->bookingData());
        } catch (SlotUnavailableException $exception) {
            return back()->withInput()->withErrors(['date' => $exception->getMessage()]);
        }

        return redirect()->to(URL::temporarySignedRoute('booking.show', now()->addDays(7), ['booking' => $booking->reference]));
    }

    public function show(Booking $booking): View
    {
        $booking->load('course');

        $seo = Seo::forPage(Page::forKey('booking'));
        $seo->noIndex = true;

        return view('pages.booking.show', ['seo' => $seo, 'booking' => $booking]);
    }
}
