@php
    $breadcrumbs = [
        ['title' => $team->name, 'url' => route('public.team.index', $team->slug)],
        ['title' => 'Polls', 'url' => route('public.team.polls.index', $team->slug)],
        ['title' => $poll->title, 'url' => '']
    ];
@endphp

<x-layouts.public :team="$team" :navigation="$navigation" :breadcrumbs="$breadcrumbs" :poll="$poll">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <article class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Poll Header -->
                    <div class="px-8 py-6 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                @if($poll->status === 'running')
                                    <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Active
                                    </span>
                                @elseif($poll->status === 'completed')
                                    <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        Completed
                                    </span>
                                @else
                                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        Upcoming
                                    </span>
                                @endif
                                <time datetime="{{ $poll->created_at->toISOString() }}" class="text-sm text-gray-500 font-medium">
                                    Created {{ $poll->created_at->format('F j, Y \\a\\t g:i A') }}
                                </time>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $poll->total_votes }} {{ Str::plural('vote', $poll->total_votes) }}
                            </div>
                        </div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $poll->title }}</h1>
                        @if($poll->description)
                            <p class="text-xl text-gray-600 leading-relaxed">{{ $poll->description }}</p>
                        @endif
                        @if($poll->ends_at)
                            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-yellow-800">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    @if($poll->status === 'running')
                                        This poll ends {{ $poll->ends_at->diffForHumans() }} ({{ $poll->ends_at->format('F j, Y \\a\\t g:i A') }})
                                    @else
                                        This poll ended {{ $poll->ends_at->diffForHumans() }}
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Poll Content -->
                    <div class="px-8 py-8">
                        @if($poll->status === 'running' && auth()->check())
                            <!-- Voting Interface -->
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Cast Your Vote</h3>
                                <form id="poll-form" class="space-y-3">
                                    @csrf
                                    @foreach($poll->options as $option)
                                        <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                            @if($poll->allows_multiple_votes)
                                                <input type="checkbox" name="options[]" value="{{ $option->id }}"
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            @else
                                                <input type="radio" name="option" value="{{ $option->id }}"
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                            @endif
                                            <span class="ml-3 text-gray-900">{{ $option->title }}</span>
                                            @if($option->description)
                                                <span class="ml-2 text-sm text-gray-500">— {{ $option->description }}</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </form>
                                <div class="mt-6">
                                    <button type="submit" form="poll-form"
                                            class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                                        Submit Vote
                                    </button>
                                    <p class="text-sm text-gray-500 mt-2">
                                        @if($poll->allows_multiple_votes)
                                            You may select multiple options.
                                        @else
                                            Please select one option.
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <hr class="my-8">
                        @elseif($poll->status === 'running' && !auth()->check())
                            <!-- Login Prompt -->
                            <div class="mb-8 p-6 bg-blue-50 border border-blue-200 rounded-lg text-center">
                                <svg class="w-12 h-12 text-blue-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Login Required</h3>
                                <p class="text-gray-600 mb-4">You must be logged in with your CMU account to participate in this poll.</p>
                                <a href="{{ route('login') }}"
                                   class="inline-flex items-center bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-2 0V5H5v10h10v-1a1 1 0 112 0v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4z"></path>
                                        <path d="M13 10a1 1 0 011-1h3a1 1 0 110 2h-3a1 1 0 01-1-1z"></path>
                                    </svg>
                                    Login with Google
                                </a>
                            </div>
                            <hr class="my-8">
                        @endif

                        <!-- Poll Results -->
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    @if($poll->status === 'running')
                                        Current Results
                                    @else
                                        Final Results
                                    @endif
                                </h3>
                                <span class="text-sm text-gray-500">
                                    Based on {{ $poll->total_votes }} {{ Str::plural('vote', $poll->total_votes) }}
                                </span>
                            </div>

                            @if($poll->total_votes > 0)
                                <div class="space-y-4">
                                    @foreach($poll->options->sortByDesc('votes') as $option)
                                        @php
                                            $percentage = ($option->votes / $poll->total_votes) * 100;
                                        @endphp
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="font-medium text-gray-900">{{ $option->title }}</h4>
                                                <div class="text-right">
                                                    <span class="text-lg font-semibold text-gray-900">{{ round($percentage, 1) }}%</span>
                                                    <span class="text-sm text-gray-500 ml-2">({{ $option->votes }} {{ Str::plural('vote', $option->votes) }})</span>
                                                </div>
                                            </div>
                                            @if($option->description)
                                                <p class="text-sm text-gray-600 mb-2">{{ $option->description }}</p>
                                            @endif
                                            <div class="w-full bg-gray-200 rounded-full h-3">
                                                <div class="bg-blue-500 h-3 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Votes Yet</h4>
                                    <p class="text-gray-600">Be the first to vote and share your opinion!</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Poll Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-600">Share this poll:</span>
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
                            <a href="{{ route('public.team.polls.index', $team->slug) }}"
                               class="text-blue-600 hover:text-blue-800 font-medium">
                                ← Back to Polls
                            </a>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Poll Info -->
                <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Poll Information</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <strong class="text-gray-700">Status:</strong>
                            <span class="ml-2">
                                @if($poll->status === 'running')
                                    <span class="text-green-600">Active</span>
                                @elseif($poll->status === 'completed')
                                    <span class="text-gray-600">Completed</span>
                                @else
                                    <span class="text-blue-600">Upcoming</span>
                                @endif
                            </span>
                        </div>
                        <div>
                            <strong class="text-gray-700">Total Votes:</strong>
                            <span class="ml-2 text-gray-600">{{ $poll->total_votes }}</span>
                        </div>
                        <div>
                            <strong class="text-gray-700">Created:</strong>
                            <span class="ml-2 text-gray-600">{{ $poll->created_at->format('M j, Y') }}</span>
                        </div>
                        @if($poll->ends_at)
                            <div>
                                <strong class="text-gray-700">
                                    @if($poll->status === 'running')
                                        Ends:
                                    @else
                                        Ended:
                                    @endif
                                </strong>
                                <span class="ml-2 text-gray-600">{{ $poll->ends_at->format('M j, Y') }}</span>
                            </div>
                        @endif
                        <div>
                            <strong class="text-gray-700">Vote Type:</strong>
                            <span class="ml-2 text-gray-600">
                                @if($poll->allows_multiple_votes)
                                    Multiple choice
                                @else
                                    Single choice
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Other Active Polls -->
                @if($otherPolls->isNotEmpty())
                    <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Other Active Polls</h3>
                        <div class="space-y-4">
                            @foreach($otherPolls as $otherPoll)
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <h4 class="font-medium text-gray-900 mb-1">
                                        <a href="{{ route('public.team.polls.show', [$team->slug, $otherPoll]) }}"
                                           class="hover:text-blue-600 transition-colors line-clamp-2">
                                            {{ $otherPoll->title }}
                                        </a>
                                    </h4>
                                    <p class="text-xs text-gray-500">{{ $otherPoll->total_votes }} votes</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('public.team.polls.index', $team->slug) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View All Polls →
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Department Info -->
                <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $team->name }}</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Participate in department discussions and help shape our community decisions.
                    </p>
                    <div class="space-y-2">
                        <a href="{{ route('public.team.index', $team->slug) }}"
                           class="block text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Department Home →
                        </a>
                        <a href="{{ route('public.team.announcements.index', $team->slug) }}"
                           class="block text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Latest News →
                        </a>
                    </div>
                </div>

                <!-- Contact -->
                @if($team->manager_email)
                    <div class="eureka-card bg-blue-50 rounded-lg border border-blue-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Questions?</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Have questions about this poll or {{ $team->name }}?
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
        // Poll voting functionality
        document.getElementById('poll-form')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const pollId = {{ $poll->id }};

            fetch(`/api/polls/${pollId}/vote`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the page to show updated results
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred while submitting your vote.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your vote.');
            });
        });

        // Social sharing functions
        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('{{ addslashes($poll->title) }}');
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