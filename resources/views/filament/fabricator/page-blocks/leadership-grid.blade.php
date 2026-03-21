@props([
    'heading' => 'Meet our leadership',
    'description' => null,
    'leaders' => []
])

<div class="leadership-grid-block py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">{{ $heading }}</h1>
            @if($description)
                <p class="text-xl text-gray-600 leading-relaxed max-w-4xl mx-auto">{{ $description }}</p>
            @endif
        </div>

        <!-- Leadership Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
            @foreach($leaders as $leader)
                @php
                    $media = isset($leader['image']) ? \Awcodes\Curator\Models\Media::find($leader['image']) : null;
                @endphp
                <div class="leadership-card">
                    <!-- Leader Info Row -->
                    <div class="flex gap-6 mb-6">
                        <!-- Profile Image -->
                        <div class="flex-shrink-0">
                            @if($media)
                                <img src="{{ $media->url }}"
                                     alt="{{ $leader['name'] ?? 'Leadership Team Member' }}"
                                     class="w-24 h-24 md:w-32 md:h-32 rounded-2xl object-cover shadow-lg">
                            @else
                                <div class="w-24 h-24 md:w-32 md:h-32 bg-gray-200 rounded-2xl flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Name & Title -->
                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">
                                {{ $leader['name'] ?? 'Leadership Team Member' }}
                            </h2>
                            <p class="text-base md:text-lg text-gray-600 font-medium">
                                {{ $leader['title'] ?? 'Leadership Team Member' }}
                            </p>
                        </div>
                    </div>

                    <!-- Biography -->
                    @if(isset($leader['bio']) && $leader['bio'])
                        <div class="mb-6">
                            <p class="text-gray-700 leading-relaxed text-base">
                                {{ $leader['bio'] }}
                            </p>
                        </div>
                    @endif

                    <!-- Social Links -->
                    <div class="flex gap-3">
                        @if(isset($leader['twitter_url']) && $leader['twitter_url'])
                            <a href="{{ $leader['twitter_url'] }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
                               aria-label="Twitter profile">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                        @endif

                        @if(isset($leader['linkedin_url']) && $leader['linkedin_url'])
                            <a href="{{ $leader['linkedin_url'] }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
                               aria-label="LinkedIn profile">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>