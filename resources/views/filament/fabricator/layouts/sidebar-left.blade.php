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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Left Sidebar -->
            <div class="lg:order-1 space-y-6">
                <!-- Table of Contents -->
                <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">On This Page</h3>
                    <div id="toc" class="text-sm">
                        <!-- TOC will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Parent Page Navigation -->
                @if($page->parent)
                    <div class="eureka-card bg-blue-50 rounded-lg border border-blue-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Parent Section</h3>
                        <div class="text-sm">
                            <a href="{{ route('public.team.page', [$team->slug, $page->parent->slug]) }}"
                               class="text-blue-600 hover:text-blue-800 font-medium">
                                ← {{ $page->parent->title }}
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Dynamic Sidebars -->
                @forelse($page->sidebars as $sidebar)
                    <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $sidebar->title }}</h3>
                        <div class="prose prose-sm max-w-none text-gray-600">
                            {!! $sidebar->content !!}
                        </div>
                    </div>
                @empty
                    <!-- Fallback: Default sidebars -->
                    <div class="eureka-card bg-gray-50 rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a href="{{ route('public.team.index', $team->slug) }}"
                                   class="text-blue-600 hover:text-blue-800">
                                    {{ $team->name }} Home
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('public.team.announcements.index', $team->slug) }}"
                                   class="text-blue-600 hover:text-blue-800">
                                    Latest News
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('public.team.polls.index', $team->slug) }}"
                                   class="text-blue-600 hover:text-blue-800">
                                    Current Polls
                                </a>
                            </li>
                        </ul>
                    </div>
                @endforelse
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3 lg:order-2">
                <article class="max-w-none">
                    <!-- Page Header (only show if no page builder blocks) -->
                    @if(!$page->blocks)
                        <header class="mb-8">
                            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $page->title }}</h1>
                            <div class="flex items-center text-sm text-gray-500">
                                <span>Last updated {{ $page->updated_at->format('F j, Y') }}</span>
                                <span class="mx-2">•</span>
                                <span>By {{ $page->author->name }}</span>
                            </div>
                        </header>
                    @endif

                    <!-- Non-hero Page Builder Blocks -->
                    @if($page->blocks)
                        <div class="space-y-8 mb-8">
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
                    <section class="mt-12 pt-8 border-t border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">In This Section</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($page->children()->published()->orderBy('sort_order')->get() as $child)
                                <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                                    <h4 class="font-semibold text-gray-900 mb-2">
                                        <a href="{{ route('public.team.page', [$team->slug, $child->slug]) }}"
                                           class="hover:text-blue-600 transition-colors">
                                            {{ $child->title }}
                                        </a>
                                    </h4>
                                    @if($child->meta_description)
                                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($child->meta_description, 100) }}</p>
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
        </div>
    </div>

    @push('scripts')
    <script>
        // Generate Table of Contents for page builder content
        document.addEventListener('DOMContentLoaded', function() {
            const tocContainer = document.getElementById('toc');
            const headings = document.querySelectorAll('.text-block h2, .text-block h3, .text-block h4');

            if (headings.length === 0) {
                tocContainer.innerHTML = '<p class="text-gray-500 text-sm italic">No headings found on this page</p>';
                return;
            }

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

                // Style based on heading level
                if (heading.tagName === 'H2') {
                    li.className = 'text-sm font-medium mb-1';
                    a.className = 'text-blue-600 hover:text-blue-800 hover:underline transition-colors';
                    li.innerHTML = '• ' + a.outerHTML;
                } else if (heading.tagName === 'H3') {
                    li.className = 'text-sm ml-4 mb-1';
                    a.className = 'text-blue-500 hover:text-blue-700 hover:underline transition-colors font-normal';
                    li.innerHTML = '• ' + a.outerHTML;
                } else if (heading.tagName === 'H4') {
                    li.className = 'text-xs ml-8 mb-1';
                    a.className = 'text-blue-400 hover:text-blue-600 hover:underline transition-colors font-normal';
                    li.innerHTML = '• ' + a.outerHTML;
                }

                li.querySelector('a').addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.getElementById(id);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        history.pushState(null, null, `#${id}`);
                    }
                });

                toc.appendChild(li);
            });

            tocContainer.appendChild(toc);
        });
    </script>
    @endpush
</x-layouts.public>