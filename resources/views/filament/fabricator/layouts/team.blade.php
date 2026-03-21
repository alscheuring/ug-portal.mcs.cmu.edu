<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?: $page->title }} - {{ $team->name }}</title>
    <meta name="description" content="{{ $page->meta_description ?: 'Learn more about our team at ' . $team->name }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Team Logo/Name -->
                <div class="flex items-center">
                    <a href="/{{ $team->slug }}" class="text-2xl font-bold text-gray-900 hover:text-blue-600 transition-colors">
                        {{ $team->name }}
                    </a>
                </div>

                <!-- Simple Navigation -->
                <nav class="hidden md:flex space-x-8">
                    @if(isset($navigation) && count($navigation) > 0)
                        @foreach($navigation as $item)
                            <a href="{{ $item['url'] }}"
                               class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors
                                      {{ request()->is(trim($item['url'], '/')) ? 'text-blue-600' : '' }}">
                                {{ $item['title'] }}
                            </a>
                        @endforeach
                    @endif
                </nav>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-600 hover:text-gray-900 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    @if($page->breadcrumbs && count($page->breadcrumbs) > 1)
        <nav class="bg-gray-100 border-b border-gray-200" aria-label="Breadcrumb">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-4 py-3">
                    @foreach($page->breadcrumbs as $index => $breadcrumb)
                        @if($loop->last)
                            <span class="text-gray-500 text-sm">{{ $breadcrumb['title'] }}</span>
                        @else
                            <a href="{{ $breadcrumb['url'] }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                {{ $breadcrumb['title'] }}
                            </a>
                            <svg class="flex-shrink-0 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    @endforeach
                </div>
            </div>
        </nav>
    @endif

    <!-- Main Content Area -->
    <main class="flex-1">
        <!-- Hero Section - Minimal for team pages -->
        <div class="bg-white py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if($page->meta_description)
                    <div class="text-center">
                        <p class="text-xl text-gray-600 max-w-3xl mx-auto">{{ $page->meta_description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Page Blocks -->
        <div class="bg-gray-50">
            @if($page->blocks)
                @foreach($page->blocks as $block)
                    @if($block['type'] === 'team-members')
                        @include('filament.fabricator.page-blocks.team-members', $block['data'])
                    @elseif($block['type'] === 'leadership-grid')
                        @include('filament.fabricator.page-blocks.leadership-grid', $block['data'])
                    @else
                        <x-filament-fabricator::page-blocks
                            :type="$block['type']"
                            :data="$block['data']"
                        />
                    @endif
                @endforeach
            @endif
        </div>

        <!-- Related Pages (if any) -->
        @if(isset($relatedPages) && $relatedPages->count() > 0)
            <section class="bg-white py-16 border-t border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Pages</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($relatedPages as $relatedPage)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    <a href="{{ $relatedPage->url }}" class="hover:text-blue-600 transition-colors">
                                        {{ $relatedPage->title }}
                                    </a>
                                </h3>
                                @if($relatedPage->meta_description)
                                    <p class="text-gray-600 text-sm">{{ Str::limit($relatedPage->meta_description, 120) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">{{ $team->contact_title ?? 'Contact Us' }}</h3>
                    @if($team->contact_content)
                        <div class="text-gray-300 space-y-2">
                            {!! nl2br($team->contact_content) !!}
                        </div>
                    @endif
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">{{ $team->quick_links_title ?? 'Quick Links' }}</h3>
                    @if($team->quick_links_content)
                        <div class="text-gray-300 space-y-2">
                            {!! nl2br($team->quick_links_content) !!}
                        </div>
                    @endif
                </div>

                <!-- Help -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">{{ $team->help_box_title ?? 'Need Help?' }}</h3>
                    @if($team->help_box_content)
                        <div class="text-gray-300 space-y-2">
                            {!! nl2br($team->help_box_content) !!}
                        </div>
                    @endif
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">&copy; {{ date('Y') }} {{ $team->name }}. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>