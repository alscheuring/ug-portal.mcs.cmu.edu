@php
    $breadcrumbs = $page->breadcrumbs;
@endphp

<x-layouts.public :team="$team" :navigation="$navigation" :breadcrumbs="$breadcrumbs" :page="$page">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <article class="prose prose-lg max-w-none">
                    <!-- Page Header -->
                    <header class="mb-8">
                        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $page->title }}</h1>
                        <div class="flex items-center text-sm text-gray-500">
                            <span>Last updated {{ $page->updated_at->format('F j, Y') }}</span>
                            <span class="mx-2">•</span>
                            <span>By {{ $page->author->name }}</span>
                        </div>
                    </header>

                    <!-- Page Content -->
                    <div class="prose prose-lg prose-blue max-w-none prose-headings:scroll-mt-6" id="page-content">
                        {!! $page->content !!}
                    </div>
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

                <!-- Related Pages -->
                @if($relatedPages->isNotEmpty())
                    <section class="mt-12 pt-8 border-t border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">
                            @if($page->parent_id)
                                Related Pages
                            @else
                                More Information
                            @endif
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($relatedPages as $related)
                                <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                                    <h4 class="font-semibold text-gray-900 mb-2">
                                        <a href="{{ route('public.team.page', [$team->slug, $related->slug]) }}"
                                           class="hover:text-blue-600 transition-colors">
                                            {{ $related->title }}
                                        </a>
                                    </h4>
                                    @if($related->meta_description)
                                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($related->meta_description, 100) }}</p>
                                    @endif
                                    <a href="{{ route('public.team.page', [$team->slug, $related->slug]) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Read More →
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
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
                        @if($page->parent->children->count() > 1)
                            <div class="mt-4">
                                <h4 class="font-medium text-gray-700 mb-2">Other Pages in This Section:</h4>
                                <ul class="space-y-1">
                                    @foreach($page->parent->children()->published()->orderBy('sort_order')->get() as $sibling)
                                        @if($sibling->id !== $page->id)
                                            <li>
                                                <a href="{{ route('public.team.page', [$team->slug, $sibling->slug]) }}"
                                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                                    {{ $sibling->title }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
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
                    <!-- Fallback: Default sidebars when no sidebars are assigned to this page -->
                    <div class="eureka-card bg-gray-50 rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $team->quick_links_title ?: 'Quick Links' }}</h3>

                        @if($team->quick_links_content)
                            <div class="prose prose-sm max-w-none text-gray-600">
                                {!! $team->quick_links_content !!}
                            </div>
                        @else
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
                        @endif
                    </div>

                    <!-- Help/Contact -->
                    @if($team->help_box_content || $team->manager_email)
                        <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $team->help_box_title ?: 'Need Help?' }}</h3>

                            @if($team->help_box_content)
                                <div class="prose prose-sm max-w-none text-gray-600">
                                    {!! $team->help_box_content !!}
                                </div>
                            @elseif($team->manager_email)
                                <p class="text-sm text-gray-600 mb-3">
                                    Have questions about this page or our department?
                                </p>
                                <a href="mailto:{{ $team->manager_email }}"
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-sm">
                                    Contact Us →
                                </a>
                            @endif
                        </div>
                    @endif
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Generate Table of Contents
        document.addEventListener('DOMContentLoaded', function() {
            const tocContainer = document.getElementById('toc');
            // Only look for headings in the main page content area, not sidebar
            const headings = document.querySelectorAll('#page-content h2, #page-content h3, #page-content h4');

            if (headings.length === 0) {
                tocContainer.innerHTML = '<p class="text-gray-500 text-sm italic">No headings found on this page</p>';
                return;
            }

            const toc = document.createElement('ul');
            toc.className = 'space-y-1 list-none text-sm';

            headings.forEach((heading, index) => {
                // Generate a clean ID from the heading text
                const cleanText = heading.textContent.trim();
                const id = heading.id || `heading-${cleanText.toLowerCase().replace(/[^\w\s-]/g, '').replace(/\s+/g, '-')}`;

                if (!heading.id) {
                    heading.id = id;
                }

                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = `#${id}`;
                a.textContent = cleanText;

                // Style based on heading level with bullet points
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

                // Add smooth scroll behavior to the recreated anchor
                li.querySelector('a').addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.getElementById(id);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        // Update URL without causing a page jump
                        history.pushState(null, null, `#${id}`);
                    }
                });

                toc.appendChild(li);
            });

            tocContainer.appendChild(toc);

            // Add some basic styling to ensure main content headings are visible
            headings.forEach(heading => {
                if (heading.tagName === 'H2') {
                    heading.className = (heading.className || '') + ' text-2xl font-bold text-gray-900 mt-8 mb-4 border-b border-gray-200 pb-2';
                } else if (heading.tagName === 'H3') {
                    heading.className = (heading.className || '') + ' text-xl font-semibold text-gray-900 mt-6 mb-3';
                } else if (heading.tagName === 'H4') {
                    heading.className = (heading.className || '') + ' text-lg font-medium text-gray-900 mt-4 mb-2';
                }
            });
        });
    </script>
    @endpush
</x-layouts.public>