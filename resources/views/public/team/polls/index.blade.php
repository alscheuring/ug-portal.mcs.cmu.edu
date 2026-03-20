<x-layouts.public :team="$team" :navigation="$navigation">
    <!-- Page Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Current Polls</h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Share your voice and participate in {{ $team->name }} community discussions
                </p>
            </div>
        </div>
    </div>

    <!-- Polls Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($polls->isNotEmpty())
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <div class="space-y-8">
                        @foreach($polls as $poll)
                            <article class="eureka-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="p-6">
                                    <!-- Poll Header -->
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center space-x-3">
                                            @if($poll->status === 'running')
                                                <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                                                    Active
                                                </span>
                                            @elseif($poll->status === 'completed')
                                                <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
                                                    Completed
                                                </span>
                                            @else
                                                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                                                    Upcoming
                                                </span>
                                            @endif
                                            <time datetime="{{ $poll->created_at->toISOString() }}" class="text-sm text-gray-500">
                                                Created {{ $poll->created_at->format('M j, Y') }}
                                            </time>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $poll->total_votes }} {{ Str::plural('vote', $poll->total_votes) }}
                                        </div>
                                    </div>

                                    <!-- Poll Title & Description -->
                                    <h2 class="text-2xl font-semibold text-gray-900 mb-3">
                                        <a href="{{ route('public.team.polls.show', [$team->slug, $poll]) }}"
                                           class="hover:text-blue-600 transition-colors">
                                            {{ $poll->title }}
                                        </a>
                                    </h2>

                                    @if($poll->description)
                                        <p class="text-gray-600 mb-4 line-clamp-3">{{ $poll->description }}</p>
                                    @endif

                                    <!-- Quick Poll Preview -->
                                    <div class="mb-6">
                                        <div class="space-y-2">
                                            @foreach($poll->options->take(3) as $option)
                                                @php
                                                    $percentage = $poll->total_votes > 0 ? ($option->votes / $poll->total_votes) * 100 : 0;
                                                @endphp
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-700 truncate flex-1 mr-4">{{ $option->title }}</span>
                                                    <div class="flex items-center space-x-2">
                                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                                        </div>
                                                        <span class="text-gray-600 w-8">{{ round($percentage) }}%</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($poll->options->count() > 3)
                                                <div class="text-sm text-gray-500 italic">
                                                    +{{ $poll->options->count() - 3 }} more options
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Poll Actions -->
                                    <div class="flex items-center justify-between">
                                        <a href="{{ route('public.team.polls.show', [$team->slug, $poll]) }}"
                                           class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                                            @if($poll->status === 'running')
                                                Vote Now
                                            @else
                                                View Results
                                            @endif
                                        </a>

                                        <div class="text-sm text-gray-500">
                                            @if($poll->ends_at && $poll->status === 'running')
                                                <span>Ends {{ $poll->ends_at->diffForHumans() }}</span>
                                            @elseif($poll->status === 'completed')
                                                <span>Ended {{ $poll->ends_at?->diffForHumans() ?? 'recently' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($polls->hasPages())
                        <div class="mt-12 flex justify-center">
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-1">
                                {{ $polls->links() }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Poll Stats -->
                    <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Poll Statistics</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Active Polls</span>
                                <span class="font-semibold text-green-600">{{ $polls->where('status', 'running')->count() }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Total Votes Cast</span>
                                <span class="font-semibold text-blue-600">{{ $polls->sum('total_votes') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Completed Polls</span>
                                <span class="font-semibold text-gray-600">{{ $polls->where('status', 'completed')->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Poll Categories -->
                    @php
                        $pollsByMonth = $polls->groupBy(function($poll) {
                            return $poll->created_at->format('Y-m');
                        });
                    @endphp
                    @if($pollsByMonth->isNotEmpty())
                        <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Browse by Month</h3>
                            <ul class="space-y-2 text-sm">
                                @foreach($pollsByMonth->take(6) as $month => $monthPolls)
                                    <li class="flex justify-between items-center">
                                        <span class="text-gray-600">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</span>
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">{{ $monthPolls->count() }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Quick Links -->
                    <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h3>
                        <ul class="space-y-3 text-sm">
                            <li>
                                <a href="{{ route('public.team.index', $team->slug) }}"
                                   class="text-blue-600 hover:text-blue-800 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    Department Home
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('public.team.announcements.index', $team->slug) }}"
                                   class="text-blue-600 hover:text-blue-800 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                    </svg>
                                    Latest News
                                </a>
                            </li>
                            @foreach($navigation as $navItem)
                                <li>
                                    <a href="{{ route('public.team.page', [$team->slug, $navItem['slug']]) }}"
                                       class="text-blue-600 hover:text-blue-800 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        {{ $navItem['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Contact -->
                    @if($team->manager_email)
                        <div class="eureka-card bg-blue-50 rounded-lg border border-blue-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Have a Question?</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Want to suggest a new poll topic or have questions about {{ $team->name }}?
                            </p>
                            <a href="mailto:{{ $team->manager_email }}"
                               class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                Contact Us
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Polls Available</h3>
                <p class="text-gray-600 max-w-md mx-auto">
                    There are no polls to participate in at this time. Check back soon for new community discussions from {{ $team->name }}.
                </p>
            </div>
        @endif
    </div>
</x-layouts.public>