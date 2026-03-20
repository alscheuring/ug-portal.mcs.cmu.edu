<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'meta_title', 'meta_description', 'content', 'is_published', 'published_at', 'team_id', 'author_id', 'parent_id', 'sort_order'])]
class Page extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = ['sidebar_ids'];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the team that owns the page.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the author of the page.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the parent page.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * Get the child pages.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get the sidebars for this page.
     */
    public function sidebars(): BelongsToMany
    {
        return $this->belongsToMany(Sidebar::class)
            ->withPivot(['sort_order'])
            ->withTimestamps()
            ->where('sidebars.is_active', true)
            ->orderBy('page_sidebar.sort_order');
    }

    /**
     * Get sidebar IDs for the form.
     */
    public function getSidebarIdsAttribute(): array
    {
        if (! $this->relationLoaded('sidebars')) {
            $this->load('sidebars');
        }

        return $this->sidebars->pluck('id')->toArray();
    }

    /**
     * Set sidebar IDs from the form.
     */
    public function setSidebarIdsAttribute($value): void
    {
        // Store the value to sync later in a model event
        $this->attributes['_sidebar_ids'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Boot method to handle sidebar syncing.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($page) {
            if (isset($page->attributes['_sidebar_ids'])) {
                $sidebarIds = json_decode($page->attributes['_sidebar_ids'], true) ?: [];

                // Create sync data with sort_order
                $syncData = [];
                foreach ($sidebarIds as $index => $sidebarId) {
                    $syncData[$sidebarId] = ['sort_order' => $index + 1];
                }

                $page->sidebars()->sync($syncData);
                unset($page->attributes['_sidebar_ids']);
            }
        });
    }

    /**
     * Scope query to only include published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope query to filter by team.
     */
    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope query to get only root pages (no parent).
     */
    public function scopeRootPages($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Generate slug from title.
     */
    public static function generateSlug(string $title, int $teamId, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)
            ->where('team_id', $teamId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the full URL for this page.
     */
    public function getUrlAttribute(): string
    {
        return "/{$this->team->slug}/{$this->slug}";
    }

    /**
     * Check if the page is published.
     */
    public function isPublished(): bool
    {
        if (! $this->is_published) {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Get the breadcrumb trail for this page.
     */
    public function getBreadcrumbsAttribute(): array
    {
        $breadcrumbs = [];
        $page = $this;

        while ($page) {
            array_unshift($breadcrumbs, [
                'title' => $page->title,
                'url' => $page->url,
            ]);
            $page = $page->parent;
        }

        // Add team home as the first breadcrumb
        array_unshift($breadcrumbs, [
            'title' => $this->team->name,
            'url' => "/{$this->team->slug}",
        ]);

        return $breadcrumbs;
    }

    /**
     * Get the navigation tree for this team.
     */
    public static function getNavigationTree(int $teamId): array
    {
        return static::forTeam($teamId)
            ->published()
            ->rootPages()
            ->orderBy('sort_order')
            ->with(['children' => function ($query) {
                $query->published()->orderBy('sort_order');
            }])
            ->get()
            ->toArray();
    }
}
