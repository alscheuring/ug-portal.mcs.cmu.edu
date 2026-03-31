<?php

namespace App\Models;

use Crumbls\Layup\Models\Page as BasePage;
use Database\Factories\LayupPageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LayupPage extends BasePage
{
    /** @use HasFactory<LayupPageFactory> */
    use HasFactory;

    protected $table = 'layup_pages';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'meta',
        'team_id',
        'author_id',
        'published_at',
        'is_department_home',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'published_at' => 'datetime',
            'is_department_home' => 'boolean',
        ];
    }

    /**
     * Automatically handle published_at when status changes.
     */
    public function setStatusAttribute($value): void
    {
        $originalStatus = $this->attributes['status'] ?? null;

        $this->attributes['status'] = $value;

        // If changing to published and published_at is not set, set it now
        if ($value === 'published' &&
            ($originalStatus !== 'published' || $this->published_at === null)) {
            $this->attributes['published_at'] = now();
        }

        // If changing from published to another status, clear published_at
        if ($value !== 'published' && $originalStatus === 'published') {
            $this->attributes['published_at'] = null;
        }
    }

    /**
     * Get the team this page belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the author of this page.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the sidebars associated with this page.
     */
    public function sidebars(): BelongsToMany
    {
        return $this->belongsToMany(Sidebar::class)
            ->withPivot(['sort_order'])
            ->withTimestamps()
            ->orderBy('layup_page_sidebar.sort_order');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForTeam($query, $team)
    {
        return $query->where('team_id', is_object($team) ? $team->id : $team);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at');
    }

    public function scopeDepartmentHome($query)
    {
        return $query->where('is_department_home', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }

    public function isDepartmentHome(): bool
    {
        return $this->is_department_home === true;
    }

    /**
     * Get the public-facing URL for this page within the team context.
     */
    public function getUrl(): string
    {
        // Department home pages are accessible directly at /{team-slug}
        if ($this->isDepartmentHome()) {
            return url("/{$this->team->slug}");
        }

        // Regular pages are at /{team-slug}/pages/{page-slug}
        return url("/{$this->team->slug}/pages/{$this->slug}");
    }

    /**
     * Publish this page.
     */
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish this page.
     */
    public function unpublish(): void
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): LayupPageFactory
    {
        return LayupPageFactory::new();
    }
}
