<?php

namespace App\Models;

use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $author
 * @property string $text
 * @property string|null $context
 * @property int $rating
 * @property bool $is_active
 * @property int $sort_order
 */
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['text', 'context'];

    protected $fillable = ['author', 'text', 'context', 'rating', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
