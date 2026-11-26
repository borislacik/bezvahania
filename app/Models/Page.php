<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * A public page with SEO metadata (and optional rich text content) editable in the admin.
 *
 * @property int $id
 * @property string $key
 * @property string $title
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $og_image
 * @property string|null $content
 */
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    use HasTranslations;

    /** Page keys mapped to the route each one belongs to. */
    public const KEYS = [
        'home' => 'home',
        'booking' => 'booking.create',
        'privacy' => 'legal.privacy',
        'terms' => 'legal.terms',
        'cancellation' => 'legal.cancellation',
        'withdrawal' => 'legal.withdrawal',
    ];

    /** @var list<string> */
    public array $translatable = ['title', 'seo_title', 'seo_description', 'content'];

    protected $fillable = ['key', 'title', 'seo_title', 'seo_description', 'og_image', 'content'];

    public static function forKey(string $key): self
    {
        return self::query()->firstOrNew(['key' => $key], ['title' => __("site.pages.{$key}")]);
    }

    public function ogImageUrl(): ?string
    {
        return $this->og_image ? Storage::disk('public')->url($this->og_image) : null;
    }
}
