<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * The requested date and time slot cannot be booked (blocked, already booked or out of range).
 */
class SlotUnavailableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('booking.errors.slot_unavailable'));
    }
}
