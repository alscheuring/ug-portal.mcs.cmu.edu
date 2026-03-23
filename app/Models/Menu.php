<?php

namespace App\Models;

use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'team_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    // Scopes
    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function getNavigationTree(): array
    {
        return $this->rootItems()
            ->with(['children' => function ($query) {
                $query->where('is_visible', true)
                    ->orderBy('sort_order')
                    ->orderBy('id');
            }])
            ->get()
            ->map(fn (MenuItem $item) => $item->toNavigationArray())
            ->toArray();
    }

    public static function getTeamNavigation(int $teamId): array
    {
        $menu = static::forTeam($teamId)->active()->first();

        if (! $menu) {
            return [];
        }

        return $menu->getNavigationTree();
    }
}
