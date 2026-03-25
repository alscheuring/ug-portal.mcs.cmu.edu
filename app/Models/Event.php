<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug) && ! empty($event->title)) {
                $event->slug = static::generateSlug($event->title);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'summary',
        'start_datetime',
        'end_datetime',
        'location',
        'info_url',
        'image_url',
        'tags',
        'is_published',
        'source_type',
        'external_id',
        'team_id',
        'author_id',
        'event_feed_id',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'is_published' => 'boolean',
            'tags' => 'array',
        ];
    }

    /**
     * Get the team that owns the event.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the author of the event.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the event feed that imported this event (if applicable).
     */
    public function eventFeed(): BelongsTo
    {
        return $this->belongsTo(EventFeed::class);
    }

    /**
     * Scope query to only include published events.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope query to filter by team.
     */
    public function scopeForTeam(Builder $query, $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope query to filter by date range.
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('start_datetime', [$startDate, $endDate]);
    }

    /**
     * Scope query to filter upcoming events.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_datetime', '>=', now());
    }

    /**
     * Scope query to filter past events.
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('end_datetime', '<', now());
    }

    /**
     * Scope query to filter by source type.
     */
    public function scopeBySourceType(Builder $query, string $sourceType): Builder
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * Scope query to filter manual events only.
     */
    public function scopeManual(Builder $query): Builder
    {
        return $query->where('source_type', 'manual');
    }

    /**
     * Scope query to filter imported events only.
     */
    public function scopeImported(Builder $query): Builder
    {
        return $query->where('source_type', 'imported');
    }

    /**
     * Scope query to filter by tag.
     */
    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Generate slug from title.
     */
    public static function generateSlug(string $title): string
    {
        return Str::slug($title);
    }

    /**
     * Get the full URL for this event.
     */
    public function getUrlAttribute(): string
    {
        return "/{$this->team->slug}/events/{$this->slug}";
    }

    /**
     * Get formatted start date.
     */
    public function getFormattedStartDateAttribute(): string
    {
        return $this->start_datetime->format('M j, Y');
    }

    /**
     * Get formatted start time.
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_datetime->format('g:i A');
    }

    /**
     * Get formatted end date.
     */
    public function getFormattedEndDateAttribute(): string
    {
        return $this->end_datetime->format('M j, Y');
    }

    /**
     * Get formatted end time.
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_datetime->format('g:i A');
    }

    /**
     * Get formatted date range.
     */
    public function getFormattedDateRangeAttribute(): string
    {
        $startDate = $this->start_datetime->format('M j, Y');
        $endDate = $this->end_datetime->format('M j, Y');

        if ($startDate === $endDate) {
            return $startDate.' • '.$this->formatted_start_time.' - '.$this->formatted_end_time;
        }

        return $startDate.' - '.$endDate;
    }

    /**
     * Check if the event is published.
     */
    public function isPublished(): bool
    {
        return $this->is_published;
    }

    /**
     * Check if the event is manually created.
     */
    public function isManual(): bool
    {
        return $this->source_type === 'manual';
    }

    /**
     * Check if the event is imported from an external feed.
     */
    public function isImported(): bool
    {
        return $this->source_type === 'imported';
    }

    /**
     * Check if the event is currently happening.
     */
    public function isHappening(): bool
    {
        $now = now();

        return $now->between($this->start_datetime, $this->end_datetime);
    }

    /**
     * Check if the event is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_datetime > now();
    }

    /**
     * Check if the event is past.
     */
    public function isPast(): bool
    {
        return $this->end_datetime < now();
    }

    /**
     * Get the duration of the event in hours.
     */
    public function getDurationInHours(): float
    {
        return $this->start_datetime->diffInHours($this->end_datetime);
    }

    /**
     * Get the duration of the event as a formatted string.
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = $this->getDurationInHours();

        if ($hours < 1) {
            $minutes = $this->start_datetime->diffInMinutes($this->end_datetime);

            return $minutes.' min';
        }

        if ($hours == 1) {
            return '1 hour';
        }

        if ($hours < 24) {
            return number_format($hours, 1).' hours';
        }

        $days = $this->start_datetime->diffInDays($this->end_datetime);

        return $days.' day'.($days > 1 ? 's' : '');
    }
}
