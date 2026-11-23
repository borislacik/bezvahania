<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * A workshop type (e.g. "KPR a záchrana života s AED").
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $badge
 * @property string $short_description
 * @property string|null $description
 * @property string $duration_label
 * @property string|null $equipment
 * @property string $price
 * @property string|null $image
 * @property bool $is_featured
 * @property bool $is_active
 * @property int $sort_order
 * @property string|null $seo_title
 * @property string|null $seo_description
 */
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    public array $translatable = [
        'name', 'badge', 'short_description', 'description', 'duration_label', 'equipment', 'seo_title', 'seo_description',
    ];

    protected $fillable = [
        'slug', 'name', 'badge', 'short_description', 'description', 'duration_label', 'equipment', 'price', 'image',
        'is_featured', 'is_active', 'sort_order', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    /**
     * Price per person for a full group of four, rounded to whole euros.
     */
    public function pricePerPerson(int $people = 4): int
    {
        return (int) round((float) $this->price / max(1, $people));
    }
}
