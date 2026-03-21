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

    <!-- Centered Content Container -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <article class="prose prose-lg prose-blue max-w-none">
            <!-- Page Header (only show if no page builder blocks) -->
            @if(!$page->blocks)
                <header class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-gray-900 mb-6">{{ $page->title }}</h1>
                    <div class="flex justify-center items-center text-sm text-gray-500">
                        <span>Last updated {{ $page->updated_at->format('F j, Y') }}</span>
                        <span class="mx-2">•</span>
                        <span>By {{ $page->author->name }}</span>
                    </div>
                </header>
            @endif

            <!-- Non-hero Page Builder Blocks -->
            @if($page->blocks)
                <div class="space-y-8" id="page-content">
                    @foreach($page->blocks as $block)
                        @if($block['type'] !== 'hero')
                            @include('filament.fabricator.page-blocks.' . $block['type'], $block['data'])
                        @endif
                    @endforeach
                </div>
            @endif
        </article>

        <!-- Child Pages Navigation -->
        @if($page->children->isNotEmpty())
            <section class="mt-16 pt-8 border-t border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900 mb-6 text-center">In This Section</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto">
                    @foreach($page->children()->published()->orderBy('sort_order')->get() as $child)
                        <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                            <h4 class="font-semibold text-gray-900 mb-3">
                                <a href="{{ route('public.team.page', [$team->slug, $child->slug]) }}"
                                   class="hover:text-blue-600 transition-colors">
                                    {{ $child->title }}
                                </a>
                            </h4>
                            @if($child->meta_description)
                                <p class="text-sm text-gray-600 mb-4">{{ Str::limit($child->meta_description, 100) }}</p>
                            @endif
                            <a href="{{ route('public.team.page', [$team->slug, $child->slug]) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Read More →
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    @push('scripts')
    <script>
        // Generate Table of Contents for page builder content
        document.addEventListener('DOMContentLoaded', function() {
            const headings = document.querySelectorAll('.text-block h2, .text-block h3, .text-block h4');

            if (headings.length > 0) {
                // Create a floating TOC for centered layout
                const tocContainer = document.createElement('div');
                tocContainer.className = 'fixed top-1/2 right-8 transform -translate-y-1/2 bg-white rounded-lg shadow-lg border border-gray-200 p-4 max-w-64 z-10 hidden lg:block';

                const tocTitle = document.createElement('h4');
                tocTitle.className = 'font-semibold text-gray-900 mb-3 text-sm';
                tocTitle.textContent = 'On This Page';
                tocContainer.appendChild(tocTitle);

                const toc = document.createElement('ul');
                toc.className = 'space-y-1 list-none text-sm';

                headings.forEach((heading) => {
                    const cleanText = heading.textContent.trim();
                    const id = heading.id || `heading-${cleanText.toLowerCase().replace(/[^\w\s-]/g, '').replace(/\s+/g, '-')}`;

                    if (!heading.id) {
                        heading.id = id;
                    }

                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.href = `#${id}`;
                    a.textContent = cleanText;
                    a.className = 'text-blue-600 hover:text-blue-800 hover:underline transition-colors';

                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        const target = document.getElementById(id);
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    });

                    li.appendChild(a);
                    toc.appendChild(li);
                });

                tocContainer.appendChild(toc);
                document.body.appendChild(tocContainer);
            }
        });
    </script>
    @endpush
</x-layouts.public>