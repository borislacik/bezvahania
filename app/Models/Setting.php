<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Simple key-value settings editable in the admin (contact details, operator data, booking rules).
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
class Setting extends Model
{
    public const DEFAULTS = [
        'contact_phone' => '+421 915 918 386',
        'contact_email' => 'info@bezvahania.sk',
        'notification_email' => 'info@bezvahania.sk',
        'operator_name' => 'Boris Lacík',
        'operator_ico' => '54842280',
        'operator_dic' => '1077502657',
        'operator_vat_note' => 'Nie som platiteľ DPH',
        'operator_address' => 'Studenohorská 2061/17, 841 03, Bratislava',
        'booking_lead_days' => '2',
        'social_facebook' => 'https://www.facebook.com/profile.php?id=61596209436961',
    ];

    private const CACHE_KEY = 'settings.all';

    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * @return array<string, string>
     */
    public static function allValues(): array
    {
        /** @var array<string, string> $stored */
        $stored = Cache::rememberForever(self::CACHE_KEY, fn (): array => self::query()
            ->pluck('value', 'key')
            ->map(fn (?string $value): string => (string) $value)
            ->all());

        return array_merge(self::DEFAULTS, $stored);
    }

    public static function get(string $key): string
    {
        return self::allValues()[$key] ?? '';
    }

    /**
     * @param  array<string, string|int|null>  $values
     */
    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            self::query()->updateOrCreate(['key' => $key], ['value' => $value === null ? null : (string) $value]);
        }
    }
}
