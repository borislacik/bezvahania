<?php

namespace App\Models;

use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A message from a contact form. `consent_at` records when the visitor sent the form with the privacy notice
 * (the forms show an information sentence, no checkbox).
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $message
 * @property Carbon|null $consent_at
 * @property Carbon|null $handled_at
 * @property Carbon $created_at
 */
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    use MassPrunable;

    /** Messages are deleted one year after they were received (GDPR). */
    public const RETENTION_MONTHS = 12;

    protected $fillable = ['name', 'email', 'phone', 'message', 'consent_at', 'handled_at'];

    protected function casts(): array
    {
        return ['consent_at' => 'datetime', 'handled_at' => 'datetime'];
    }

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<', now()->subMonths(self::RETENTION_MONTHS));
    }
}
