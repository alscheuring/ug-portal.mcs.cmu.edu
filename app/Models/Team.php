<?php

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'manager_email',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the users that belong to this team.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'current_team_id');
    }

    /**
     * Get the Layup pages that belong to this team.
     */
    public function layupPages(): HasMany
    {
        return $this->hasMany(LayupPage::class);
    }

    /**
     * Get the events that belong to this team.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the event feeds that belong to this team.
     */
    public function eventFeeds(): HasMany
    {
        return $this->hasMany(EventFeed::class);
    }
}
