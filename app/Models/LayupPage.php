<?php

namespace App\Models;

use Crumbls\Layup\Models\Page as BasePage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LayupPage extends BasePage
{
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
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'published_at' => 'datetime',
        ];
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

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }

    /**
     * Get the public-facing URL for this page within the team context.
     */
    public function getUrl(): string
    {
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
}