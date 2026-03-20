<x-layouts.public :team="$team" :navigation="$navigation">
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Column -->
            <div class="lg:col-span-2 space-y-12">
                <!-- Recent Announcements -->
                @if($announcements->isNotEmpty())
                    <section>
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-bold text-gray-900">Latest News</h2>
                            <a href="{{ route('public.team.announcements.index', $team->slug) }}"
                               class="text-blue-600 hover:text-blue-800 font-semibold">View All</a>
                        </div>
                        <div class="space-y-6">
                            @foreach($announcements as $announcement)
                                <article class="eureka-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="p-6">
                                        <div class="flex items-center text-sm text-gray-500 mb-3">
                                            <time datetime="{{ $announcement->published_at->toISOString() }}">
                                                {{ $announcement->published_at->format('F j, Y') }}
                                            </time>
                                            <span class="mx-2">•</span>
                                            <span>{{ $announcement->author->name }}</span>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-900 mb-3">
                                            <a href="{{ route('public.team.announcements.show', [$team->slug, $announcement->slug]) }}"
                                               class="hover:text-blue-600 transition-colors">
                                                {{ $announcement->title }}
                                            </a>
                                        </h3>
                                        @if($announcement->excerpt)
                                            <p class="text-gray-600 mb-4">{{ $announcement->excerpt }}</p>
                                        @endif
                                        <a href="{{ route('public.team.announcements.show', [$team->slug, $announcement->slug]) }}"
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            Read More →
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- Featured Pages -->
                @if($featuredPages->isNotEmpty())
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-8">Explore Our Department</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @foreach($featuredPages as $page)
                                <div class="eureka-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">
                                        <a href="{{ route('public.team.page', [$team->slug, $page->slug]) }}"
                                           class="hover:text-blue-600 transition-colors">
                                            {{ $page->title }}
                                        </a>
                                    </h3>
                                    @if($page->meta_description)
                                        <p class="text-gray-600 mb-4">{{ Str::limit($page->meta_description, 120) }}</p>
                                    @endif
                                    <a href="{{ route('public.team.page', [$team->slug, $page->slug]) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium">
                                        Learn More →
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Quick Stats Card -->
                <div class="eureka-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">At a Glance</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Recent News</span>
                            <span class="font-semibold text-blue-600">{{ $announcements->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Active Polls</span>
                            <span class="font-semibold text-green-600">{{ $polls->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Pages</span>
                            <span class="font-semibold text-purple-600">{{ $featuredPages->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Active Polls -->
                @if($polls->isNotEmpty())
                    <div class="eureka-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Active Polls</h3>
                            <a href="{{ route('public.team.polls.index', $team->slug) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
                        </div>
                        <div class="space-y-4">
                            @foreach($polls->take(3) as $poll)
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <h4 class="font-medium text-gray-900 mb-1">
                                        <a href="{{ route('public.team.polls.show', [$team->slug, $poll]) }}"
                                           class="hover:text-blue-600 transition-colors">
                                            {{ $poll->title }}
                                        </a>
                                    </h4>
                                    <p class="text-sm text-gray-600">{{ $poll->total_votes }} votes</p>
                                    @if($poll->ends_at)
                                        <p class="text-xs text-gray-500 mt-1">
                                            Ends {{ $poll->ends_at->diffForHumans() }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Contact Information -->
                <div class="eureka-card bg-gray-50 rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $team->contact_title ?: 'Get in Touch' }}</h3>

                    @if($team->contact_content)
                        <div class="prose prose-sm max-w-none text-gray-600">
                            {!! $team->contact_content !!}
                        </div>
                    @else
                        <div class="space-y-3 text-sm">
                            <div>
                                <strong class="text-gray-700">Location:</strong><br>
                                <span class="text-gray-600">
                                    Carnegie Mellon University<br>
                                    Mellon College of Science<br>
                                    Pittsburgh, PA 15213
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>