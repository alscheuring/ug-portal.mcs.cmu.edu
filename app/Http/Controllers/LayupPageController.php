<?php

namespace App\Http\Controllers;

use App\Models\LayupPage;
use App\Models\Menu;
use App\Models\Team;
use Illuminate\View\View;

class LayupPageController extends Controller
{
    /**
     * Display a listing of the team's published pages.
     */
    public function index(Team $team): View
    {
        $pages = LayupPage::query()
            ->forTeam($team)
            ->published()
            ->orderBy('title')
            ->get();

        // Get navigation from Menu Manager
        $navigation = Menu::getTeamNavigation($team->id);

        return view('layup-pages.index', compact('team', 'pages', 'navigation'));
    }

    /**
     * Display the specified page.
     */
    public function show(Team $team, string $slug): View
    {
        $page = LayupPage::query()
            ->forTeam($team)
            ->published()
            ->where('slug', $slug)
            ->with('sidebars')
            ->firstOrFail();

        // Get navigation from Menu Manager
        $navigation = Menu::getTeamNavigation($team->id);

        return view('layup-pages.show', compact('team', 'page', 'navigation'));
    }
}
