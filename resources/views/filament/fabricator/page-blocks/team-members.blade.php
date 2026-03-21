@props([
    'heading' => 'About the team',
    'description' => null,
    'members' => []
])

<div class="team-members-block py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16">
            <!-- Left Column - Heading & Description -->
            <div class="lg:col-span-4">
                <div class="sticky top-8">
                    <h2 class="text-4xl font-bold text-gray-900 mb-6">{{ $heading }}</h2>
                    @if($description)
                        <p class="text-lg text-gray-600 leading-relaxed">{{ $description }}</p>
                    @endif
                </div>
            </div>

            <!-- Right Column - Team Members -->
            <div class="lg:col-span-8">
                <div class="space-y-12">
                    @foreach($members as $member)
                        @php
                            $media = isset($member['image']) ? \Awcodes\Curator\Models\Media::find($member['image']) : null;
                        @endphp
                        <div class="flex flex-col sm:flex-row gap-6 items-start">
                            <!-- Profile Image -->
                            <div class="flex-shrink-0">
                                @if($media)
                                    <img src="{{ $media->url }}"
                                         alt="{{ $member['name'] ?? 'Team Member' }}"
                                         class="w-24 h-24 sm:w-32 sm:h-32 rounded-2xl object-cover shadow-lg">
                                @else
                                    <div class="w-24 h-24 sm:w-32 sm:h-32 bg-gray-200 rounded-2xl flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Member Info -->
                            <div class="flex-1 min-w-0">
                                <div class="mb-4">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-1">
                                        {{ $member['name'] ?? 'Team Member' }}
                                    </h3>
                                    <p class="text-base text-gray-600 font-medium">
                                        {{ $member['title'] ?? 'Team Member' }}
                                    </p>
                                </div>

                                @if(isset($member['bio']) && $member['bio'])
                                    <p class="text-gray-700 leading-relaxed mb-4">
                                        {{ $member['bio'] }}
                                    </p>
                                @endif

                                <!-- Social Links -->
                                <div class="flex gap-3">
                                    @if(isset($member['twitter_url']) && $member['twitter_url'])
                                        <a href="{{ $member['twitter_url'] }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
                                           aria-label="Twitter profile">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(isset($member['linkedin_url']) && $member['linkedin_url'])
                                        <a href="{{ $member['linkedin_url'] }}"
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
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>