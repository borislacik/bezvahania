<?php

namespace App\Models;

use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $name
 * @property string $role
 * @property string|null $quote
 * @property string|null $bio
 * @property string|null $photo
 * @property bool $is_active
 * @property int $sort_order
 */
class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['role', 'quote', 'bio'];

    protected $fillable = ['name', 'role', 'quote', 'bio', 'photo', 'is_active', 'sort_order'];

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

    public function photoUrl(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }
}
