<x-layouts.public :team="$team" :navigation="$navigation" :title="'Pages - ' . $team->name">
    <x-slot name="description">Browse all pages for {{ $team->name }}</x-slot>
    <div class="min-h-screen bg-gray-50">
        {{-- Page Header --}}
        <div class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="py-6">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-4">
                            <li>
                                <div>
                                    <a href="{{ route('public.team.index', $team) }}" class="text-gray-400 hover:text-gray-500">
                                        {{ $team->name }}
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                                    </svg>
                                    <span class="ml-4 text-sm font-medium text-gray-700">Pages</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <div class="mt-2">
                        <h1 class="text-3xl font-bold text-gray-900">Pages</h1>
                        <p class="text-gray-600">Browse all published pages for {{ $team->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page Content --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if($pages->count() > 0)
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($pages as $page)
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    <a href="{{ route('public.pages.show', [$team, $page->slug]) }}" class="hover:text-blue-600">
                                        {{ $page->title }}
                                    </a>
                                </h3>

                                {{-- Page Meta Description --}}
                                @if($page->getMetaDescription())
                                    <p class="text-gray-600 mb-4 line-clamp-3">{{ $page->getMetaDescription() }}</p>
                                @endif

                                {{-- Page Footer --}}
                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <span>{{ $page->published_at->format('M j, Y') }}</span>
                                    <a href="{{ route('public.pages.show', [$team, $page->slug]) }}"
                                       class="text-blue-600 hover:text-blue-700 font-medium">
                                        Read more →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No pages</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $team->name }} hasn't published any pages yet.</p>
                    <div class="mt-6">
                        <a href="{{ route('public.team.index', $team) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            ← Back to {{ $team->name }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.public>