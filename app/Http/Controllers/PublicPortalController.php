<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\LayupPage;
use App\Models\Menu;
use App\Models\Poll;
use App\Models\Team;

class PublicPortalController extends Controller
{
    /**
     * Display the team portal homepage or department home page if one exists.
     */
    public function indexOrPage(Team $team)
    {
        // Check if there's a department home page for this team
        $departmentHomePage = LayupPage::where('team_id', $team->id)
            ->where('slug', $team->slug)
            ->where('is_department_home', true)
            ->published()
            ->first();

        if ($departmentHomePage) {
            // Serve the department home page
            return $this->renderDepartmentHomePage($team, $departmentHomePage);
        }

        // Fall back to the original team portal index
        return $this->index($team);
    }

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

        // Get featured pages
        $featuredPages = LayupPage::forTeam($team->id)
            ->published()
            ->orderBy('title')
            ->limit(6)
            ->get();

        return view('public.team.index', compact('team', 'announcements', 'polls', 'navigation', 'featuredPages'));
    }

    /**
     * Display a specific page (redirects to new Layup page controller).
     */
    public function page(Team $team, string $slug)
    {
        // Redirect to the new Layup page route
        return redirect()->route('public.pages.show', [$team->slug, $slug]);
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

    /**
     * Render a department home page using the LayupPage view.
     */
    private function renderDepartmentHomePage(Team $team, LayupPage $page)
    {
        // Get navigation from Menu Manager
        $navigation = Menu::getTeamNavigation($team->id);

        return view('layup-pages.show', compact('team', 'page', 'navigation'));
    }
}
