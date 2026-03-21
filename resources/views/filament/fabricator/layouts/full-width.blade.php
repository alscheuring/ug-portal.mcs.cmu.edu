@php
    $breadcrumbs = $page->breadcrumbs;
    $team = $page->team;
    $navigation = [];
@endphp

<x-layouts.public :team="$team" :navigation="$navigation" :breadcrumbs="$breadcrumbs" :page="$page">
    <!-- Full-width hero blocks -->
    @if($page->blocks)
        @foreach($page->blocks as $block)
            @if($block['type'] === 'hero')
                @include('filament.fabricator.page-blocks.' . $block['type'], $block['data'])
            @endif
        @endforeach
    @endif

    <!-- Full Width Content Container -->
    <div class="max-w-none">
        <!-- Non-hero Page Builder Blocks -->
        @if($page->blocks)
            <div class="space-y-0">
                @foreach($page->blocks as $block)
                    @if($block['type'] !== 'hero')
                        @include('filament.fabricator.page-blocks.' . $block['type'], $block['data'])
                    @endif
                @endforeach
            </div>
        @endif

        <!-- Page Header (only show if no page builder blocks) -->
        @if(!$page->blocks)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <header class="text-center mb-12">
                    <h1 class="text-5xl font-bold text-gray-900 mb-6">{{ $page->title }}</h1>
                    <div class="flex justify-center items-center text-sm text-gray-500">
                        <span>Last updated {{ $page->updated_at->format('F j, Y') }}</span>
                        <span class="mx-2">•</span>
                        <span>By {{ $page->author->name }}</span>
                    </div>
                </header>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        // Smooth scroll for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts.public>