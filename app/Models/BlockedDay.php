<?php

namespace App\Models;

use Database\Factories\BlockedDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The instructor is not available: the whole day or one slot (morning / afternoon) is greyed out
 * in the public calendar.
 *
 * @property int $id
 * @property Carbon $date
 * @property string $slot "day" or a TimeSlot value
 * @property string|null $reason
 */
class BlockedDay extends Model
{
    /** @use HasFactory<BlockedDayFactory> */
    use HasFactory;

    /** Value of "slot" for a block of the whole day. */
    public const WHOLE_DAY = 'day';

    protected $fillable = ['date', 'slot', 'reason'];

    protected $attributes = ['slot' => self::WHOLE_DAY];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
