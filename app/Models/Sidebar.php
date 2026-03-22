<?php

namespace App\Models;

use Database\Factories\SidebarFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sidebar extends Model
{
    /** @use HasFactory<SidebarFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'title',
        'content',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(LayupPage::class)
            ->withPivot(['sort_order'])
            ->withTimestamps()
            ->orderBy('page_sidebar.sort_order');
    }
}
