<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'link_type',
        'page_id',
        'external_url',
        'opens_in_new_tab',
        'sort_order',
        'is_visible',
        'description',
        'css_class',
        'icon',
    ];

    protected $casts = [
        'opens_in_new_tab' => 'boolean',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(LayupPage::class);
    }

    // Scopes
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeRootItems(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    // Methods
    public function getUrl(): ?string
    {
        switch ($this->link_type) {
            case 'page':
                if ($this->page) {
                    return route('public.pages.show', [
                        $this->menu->team->slug,
                        $this->page->slug,
                    ]);
                }
                break;

            case 'external':
                return $this->external_url;

            case 'announcements':
                return route('public.team.announcements.index', $this->menu->team->slug);

            case 'polls':
                return route('public.team.polls.index', $this->menu->team->slug);

            case 'divider':
                return null;

            case 'parent':
                return null;
        }

        return null;
    }

    public function toNavigationArray(): array
    {
        $data = [
            'title' => $this->title,
            'url' => $this->getUrl(),
            'type' => $this->link_type,
            'opens_in_new_tab' => $this->opens_in_new_tab,
            'css_class' => $this->css_class,
            'icon' => $this->icon,
            'description' => $this->description,
        ];

        if ($this->children->isNotEmpty()) {
            $data['children'] = $this->children->map(fn (MenuItem $child) => $child->toNavigationArray())->toArray();
        }

        return $data;
    }

    public function isActive(string $currentUrl): bool
    {
        $menuUrl = $this->getUrl();

        if (! $menuUrl) {
            return false;
        }

        // Check if current URL matches this menu item
        if ($menuUrl === $currentUrl) {
            return true;
        }

        // Check children for active state
        foreach ($this->children as $child) {
            if ($child->isActive($currentUrl)) {
                return true;
            }
        }

        return false;
    }
}
