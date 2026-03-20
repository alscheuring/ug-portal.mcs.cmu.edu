@php
    $breadcrumbs = [
        ['title' => $team->name, 'url' => route('public.team.index', $team->slug)],
        ['title' => 'News', 'url' => route('public.team.announcements.index', $team->slug)],
        ['title' => $announcement->title, 'url' => '']
    ];
@endphp

<x-layouts.public :team="$team" :navigation="$navigation" :breadcrumbs="$breadcrumbs" :announcement="$announcement">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <article class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Article Header -->
                    <div class="px-8 py-6 border-b border-gray-100">
                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <time datetime="{{ $announcement->published_at->toISOString() }}" class="font-medium">
                                {{ $announcement->published_at->format('F j, Y \a\t g:i A') }}
                            </time>
                            <span class="mx-2">•</span>
                            <span>By {{ $announcement->author->name }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ $announcement->published_at->diffForHumans() }}</span>
                        </div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $announcement->title }}</h1>
                        @if($announcement->excerpt)
                            <p class="text-xl text-gray-600 leading-relaxed">{{ $announcement->excerpt }}</p>
                        @endif
                    </div>

                    <!-- Article Content -->
                    <div class="px-8 py-8">
                        <div class="prose prose-lg prose-blue max-w-none">
                            {!! $announcement->content !!}
                        </div>
                    </div>

                    <!-- Article Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-600">Share this article:</span>
                                <div class="flex space-x-2">
                                    <button onclick="shareOnTwitter()"
                                            class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                        </svg>
                                    </button>
                                    <button onclick="shareOnLinkedIn()"
                                            class="bg-blue-700 hover:bg-blue-800 text-white p-2 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                        </svg>
                                    </button>
                                    <button onclick="copyToClipboard()"
                                            class="bg-gray-600 hover:bg-gray-700 text-white p-2 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <a href="{{ route('public.team.announcements.index', $team->slug) }}"
                               class="text-blue-600 hover:text-blue-800 font-medium">
                                ← Back to News
                            </a>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Recent Announcements -->
                @if($recentAnnouncements->isNotEmpty())
                    <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent News</h3>
                        <div class="space-y-4">
                            @foreach($recentAnnouncements as $recent)
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <h4 class="font-medium text-gray-900 mb-1">
                                        <a href="{{ route('public.team.announcements.show', [$team->slug, $recent->slug]) }}"
                                           class="hover:text-blue-600 transition-colors line-clamp-2">
                                            {{ $recent->title }}
                                        </a>
                                    </h4>
                                    <p class="text-xs text-gray-500">{{ $recent->published_at->format('M j, Y') }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('public.team.announcements.index', $team->slug) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View All News →
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Author Info -->
                <div class="eureka-card bg-gray-50 rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">About the Author</h3>
                    <div class="flex items-start space-x-3">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-semibold text-lg">
                                {{ $announcement->author->initials() }}
                            </span>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $announcement->author->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $team->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Department Info -->
                <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $team->name }}</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Explore more content from our department including research, programs, and faculty information.
                    </p>
                    <div class="space-y-2">
                        <a href="{{ route('public.team.index', $team->slug) }}"
                           class="block text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Department Home →
                        </a>
                        <a href="{{ route('public.team.polls.index', $team->slug) }}"
                           class="block text-blue-600 hover:text-blue-800 text-sm font-medium">
                           Current Polls →
                        </a>
                    </div>
                </div>

                <!-- Contact -->
                @if($team->manager_email)
                    <div class="eureka-card bg-blue-50 rounded-lg border border-blue-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Questions?</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Have questions about this announcement or {{ $team->name }}?
                        </p>
                        <a href="mailto:{{ $team->manager_email }}"
                           class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-sm">
                            Contact Us →
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('{{ addslashes($announcement->title) }}');
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank', 'width=550,height=420');
        }

        function shareOnLinkedIn() {
            const url = encodeURIComponent(window.location.href);
            window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank', 'width=550,height=420');
        }

        function copyToClipboard() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                // Show a temporary notification
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                }, 2000);
            });
        }
    </script>
    @endpush
</x-layouts.public>