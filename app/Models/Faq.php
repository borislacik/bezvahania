<?php

namespace App\Models;

use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property bool $is_active
 * @property int $sort_order
 */
class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['question', 'answer'];

    protected $fillable = ['question', 'answer', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
