<x-layouts.public :team="$team" :navigation="$navigation">
    <!-- Page Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Latest News</h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Stay up to date with the latest announcements and news from {{ $team->name }}
                </p>
            </div>
        </div>
    </div>

    <!-- Announcements Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($announcements->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($announcements as $announcement)
                    <article class="eureka-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <!-- Date and Author -->
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <time datetime="{{ $announcement->published_at->toISOString() }}" class="font-medium">
                                    {{ $announcement->published_at->format('M j, Y') }}
                                </time>
                                <span class="mx-2">•</span>
                                <span>{{ $announcement->author->name }}</span>
                            </div>

                            <!-- Title -->
                            <h2 class="text-xl font-semibold text-gray-900 mb-3">
                                <a href="{{ route('public.team.announcements.show', [$team->slug, $announcement->slug]) }}"
                                   class="hover:text-blue-600 transition-colors">
                                    {{ $announcement->title }}
                                </a>
                            </h2>

                            <!-- Excerpt -->
                            @if($announcement->excerpt)
                                <p class="text-gray-600 mb-4 line-clamp-3">{{ $announcement->excerpt }}</p>
                            @else
                                <p class="text-gray-600 mb-4 line-clamp-3">{{ Str::limit(strip_tags($announcement->content), 120) }}</p>
                            @endif

                            <!-- Read More -->
                            <div class="flex items-center justify-between">
                                <a href="{{ route('public.team.announcements.show', [$team->slug, $announcement->slug]) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                    Read More →
                                </a>
                                <div class="text-sm text-gray-400">
                                    {{ $announcement->published_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($announcements->hasPages())
                <div class="mt-12 flex justify-center">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-1">
                        {{ $announcements->links() }}
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No News Yet</h3>
                <p class="text-gray-600 max-w-md mx-auto">
                    There are no announcements to display at this time. Check back soon for the latest news from {{ $team->name }}.
                </p>
            </div>
        @endif
    </div>

    <!-- Side Content -->
    <div class="bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Archive Links -->
                <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Browse by Month</h3>
                    @php
                        $archiveMonths = $announcements->groupBy(function($announcement) {
                            return $announcement->published_at->format('Y-m');
                        });
                    @endphp
                    @if($archiveMonths->isNotEmpty())
                        <ul class="space-y-2 text-sm">
                            @foreach($archiveMonths->take(6) as $month => $monthAnnouncements)
                                <li class="flex justify-between items-center">
                                    <span class="text-gray-600">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</span>
                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">{{ $monthAnnouncements->count() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500 text-sm">No archive available</p>
                    @endif
                </div>

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
                            <a href="{{ route('public.team.polls.index', $team->slug) }}"
                               class="text-blue-600 hover:text-blue-800 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Current Polls
                            </a>
                        </li>
                        @foreach($navigation as $navItem)
                            @if($navItem['type'] !== 'divider' && $navItem['url'])
                                <li>
                                    <a href="{{ $navItem['url'] }}"
                                       class="text-blue-600 hover:text-blue-800 flex items-center"
                                       @if($navItem['opens_in_new_tab'] ?? false) target="_blank" rel="noopener" @endif>
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        {{ $navItem['title'] }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <!-- Contact -->
                @if($team->manager_email)
                    <div class="eureka-card bg-blue-50 rounded-lg border border-blue-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Stay Connected</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Have news to share or questions about {{ $team->name }}?
                        </p>
                        <a href="mailto:{{ $team->manager_email }}"
                           class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                            Contact Us
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.public>