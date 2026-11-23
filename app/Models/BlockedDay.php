<?php

namespace App\Models;

use Database\Factories\BlockedDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A day the instructor is not available; shown greyed out in the public calendar.
 *
 * @property int $id
 * @property Carbon $date
 * @property string|null $reason
 */
class BlockedDay extends Model
{
    /** @use HasFactory<BlockedDayFactory> */
    use HasFactory;

    protected $fillable = ['date', 'reason'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
