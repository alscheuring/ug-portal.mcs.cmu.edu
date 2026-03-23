<x-layouts.public :team="$team" :navigation="$navigation" :title="$page->getMetaTitle()">
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/crumbls/layup/layup.css') }}">
        @if($page->getMetaKeywords())
            <meta name="keywords" content="{{ $page->getMetaKeywords() }}">
        @endif
        {{-- Structured Data --}}
        @foreach($page->getStructuredData() as $schema)
            <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
        @endforeach
    </x-slot>
    <div class="min-h-screen">
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
                                    <a href="{{ route('public.pages.index', $team) }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Pages</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                                    </svg>
                                    <span class="ml-4 text-sm font-medium text-gray-700">{{ $page->title }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <div class="mt-2">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $page->title }}</h1>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page Content --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if($page->sidebars->isNotEmpty())
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    {{-- Main Content --}}
                    <div class="lg:col-span-3">
                        {{-- Render Layup Content --}}
                        @if($page->content && is_array($page->content))
                            @php
                                // Only use the top-level rows (ignore default hero section)
                                $content = $page->content;

                                // If we have top-level rows, use only those
                                if (isset($content['rows']) && is_array($content['rows']) && !empty($content['rows'])) {
                                    $content = ['sections' => [['rows' => $content['rows']]]];
                                }
                                // Otherwise, fall back to existing sections if no top-level rows
                                elseif (isset($content['sections'])) {
                                    $content = ['sections' => $content['sections']];
                                }
                            @endphp
                            @layup($content)
                        @else
                            <div class="prose max-w-none">
                                <p class="text-gray-600">This page has no content yet.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Sidebar --}}
                    <div class="space-y-6">
                        {{-- Dynamic Sidebars --}}
                        @foreach($page->sidebars as $sidebar)
                            <div class="eureka-card bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $sidebar->title }}</h3>
                                <div class="prose prose-sm max-w-none text-gray-600">
                                    {!! $sidebar->content !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- No sidebars: full-width content --}}
                <div>
                    {{-- Render Layup Content --}}
                    @if($page->content && is_array($page->content))
                        @php
                            // Only use the top-level rows (ignore default hero section)
                            $content = $page->content;

                            // If we have top-level rows, use only those
                            if (isset($content['rows']) && is_array($content['rows']) && !empty($content['rows'])) {
                                $content = ['sections' => [['rows' => $content['rows']]]];
                            }
                            // Otherwise, fall back to existing sections if no top-level rows
                            elseif (isset($content['sections'])) {
                                $content = ['sections' => $content['sections']];
                            }
                        @endphp
                        @layup($content)
                    @else
                        <div class="prose max-w-none">
                            <p class="text-gray-600">This page has no content yet.</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Page Meta Information --}}
        @if($page->updated_at)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    Last updated: {{ $page->updated_at->format('F j, Y') }}
                </p>
            </div>
        @endif
    </div>

    {{-- Include Layup scripts --}}
    <x-slot name="scripts">
        @layupScripts
    </x-slot>
</x-layouts.public>