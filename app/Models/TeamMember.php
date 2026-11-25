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
 * @property list<string>|null $gallery more photos shown in turn after the main one
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

    protected $fillable = ['name', 'role', 'quote', 'bio', 'photo', 'gallery', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['gallery' => 'array', 'is_active' => 'boolean', 'sort_order' => 'integer'];
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

    /**
     * The main photo followed by the gallery, as public URLs.
     *
     * @return list<string>
     */
    public function photoUrls(): array
    {
        $paths = array_filter([$this->photo, ...($this->gallery ?? [])], fn (mixed $path): bool => is_string($path) && $path !== '');

        return array_values(array_map(fn (string $path): string => Storage::disk('public')->url($path), $paths));
    }
}
