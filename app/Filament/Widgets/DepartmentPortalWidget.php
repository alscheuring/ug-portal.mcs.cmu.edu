<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DepartmentPortalWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -2;

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->isTeamAdmin() && Auth::user()->team;
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $team = $user->team;

        if (! $team) {
            return [];
        }

        return [
            Stat::make('Welcome, '.explode(' ', $user->name)[0].'!', $team->name.' Admin')
                ->description('You are managing content for '.$team->name.' department')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),

            Stat::make('Student Portal', '→ View Live Site')
                ->description('See how students experience your content at /'.$team->slug)
                ->descriptionIcon('heroicon-m-arrow-top-right-on-square')
                ->color('primary')
                ->url(route('public.team.index', $team->slug))
                ->openUrlInNewTab()
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-blue-50 transition-colors',
                ]),
        ];
    }
}
