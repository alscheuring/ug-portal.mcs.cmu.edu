<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'excerpt', 'content', 'is_published', 'published_at', 'team_id', 'author_id'])]
class Announcement extends Model
{
    use HasFactory, SoftDeletes;

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
     * Get the team that owns the announcement.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the author of the announcement.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope query to only include published announcements.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    /**
     * Scope query to filter by team.
     */
    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Generate slug from title.
     */
    public static function generateSlug(string $title): string
    {
        return Str::slug($title);
    }

    /**
     * Get the full URL for this announcement.
     */
    public function getUrlAttribute(): string
    {
        return "/{$this->team->slug}/announcements/{$this->slug}";
    }

    /**
     * Check if the announcement is published.
     */
    public function isPublished(): bool
    {
        return $this->is_published && $this->published_at <= now();
    }
}
