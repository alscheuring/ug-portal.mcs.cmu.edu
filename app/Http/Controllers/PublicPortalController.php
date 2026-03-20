<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Poll;
use App\Models\Team;

class PublicPortalController extends Controller
{
    /**
     * Display the team portal homepage.
     */
    public function index(Team $team)
    {
        // Get recent announcements
        $announcements = Announcement::forTeam($team->id)
            ->published()
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        // Get active polls
        $polls = Poll::forTeam($team->id)
            ->running()
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->with('options')
            ->get();

        // Get navigation from Menu Manager
        $navigation = Menu::getTeamNavigation($team->id);

        // Get featured pages (root pages)
        $featuredPages = Page::forTeam($team->id)
            ->published()
            ->rootPages()
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return view('public.team.index', compact('team', 'announcements', 'polls', 'navigation', 'featuredPages'));
    }

    /**
     * Display a specific page.
     */
    public function page(Team $team, string $slug)
    {
        $page = Page::forTeam($team->id)
            ->published()
            ->where('slug', $slug)
            ->with('sidebars')
            ->firstOrFail();

        // Get navigation from Menu Manager
        $navigation = Menu::getTeamNavigation($team->id);

        // Get related pages (siblings or children)
        $relatedPages = [];
        if ($page->parent_id) {
            // Get sibling pages
            $relatedPages = Page::forTeam($team->id)
                ->published()
                ->where('parent_id', $page->parent_id)
                ->where('id', '!=', $page->id)
                ->orderBy('sort_order')
                ->limit(4)
                ->get();
        } else {
            // Get child pages
            $relatedPages = $page->children()
                ->published()
                ->orderBy('sort_order')
                ->limit(4)
                ->get();
        }

        return view('public.team.page', compact('team', 'page', 'navigation', 'relatedPages'));
    }

    /**
     * Display announcements listing.
     */
    public function announcements(Team $team)
    {
        $announcements = Announcement::forTeam($team->id)
            ->published()
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        // Get navigation from Menu Manager
        $navigation = Menu::getTeamNavigation($team->id);

        return view('public.team.announcements.index', compact('team', 'announcements', 'navigation'));
    }

    /**
     * Display a specific announcement.
     */
    public function announcement(Team $team, string $slug)
    {
        $announcement = Announcement::forTeam($team->id)
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        // Get recent announcements for sidebar
        $recentAnnouncements = Announcement::forTeam($team->id)
            ->published()
            ->where('id', '!=', $announcement->id)
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        // Get navigation from Menu Manager
        $navigation = Menu::getTeamNavigation($team->id);

        return view('public.team.announcements.show', compact('team', 'announcement', 'recentAnnouncements', 'navigation'));
    }

    /**
     * Display polls listing.
     */
    public function polls(Team $team)
    {
        $activePolls = Poll::forTeam($team->id)
            ->running()
            ->orderBy('created_at', 'desc')
            ->with('options')
            ->get();

        $pastPolls = Poll::forTeam($team->id)
            ->where('is_active', true)
            ->where('ends_at', '<', now())
            ->orderBy('ends_at', 'desc')
            ->with('options')
            ->limit(10)
            ->get();

        // Get navigation from Menu Manager
        $navigation = Menu::getTeamNavigation($team->id);

        // Combine active and past polls for display
        $polls = $activePolls->concat($pastPolls);

        return view('public.team.polls.index', compact('team', 'polls', 'navigation'));
    }

    /**
     * Display a specific poll.
     */
    public function poll(Team $team, Poll $poll)
    {
        // Ensure poll belongs to team
        abort_unless($poll->team_id === $team->id, 404);

        // Load poll with options and vote counts
        $poll->load(['options', 'votes']);

        // Check if user has voted (if authenticated)
        $userHasVoted = false;
        $userVotes = [];
        if (auth()->check()) {
            $userVotes = $poll->votes()
                ->where('user_id', auth()->id())
                ->pluck('poll_option_id')
                ->toArray();
            $userHasVoted = ! empty($userVotes);
        }

        // Get other active polls for sidebar
        $otherPolls = Poll::forTeam($team->id)
            ->running()
            ->where('id', '!=', $poll->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Get navigation from Menu Manager
        $navigation = Menu::getTeamNavigation($team->id);

        return view('public.team.polls.show', compact('team', 'poll', 'userHasVoted', 'userVotes', 'otherPolls', 'navigation'));
    }
}
