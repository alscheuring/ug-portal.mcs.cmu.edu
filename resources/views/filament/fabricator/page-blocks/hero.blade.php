@props([
    'title' => null,
    'subtitle' => null,
    'button_text' => null,
    'button_link' => null,
    'background_image' => null,
])

@php
    $backgroundMedia = $background_image ? \Awcodes\Curator\Models\Media::find($background_image) : null;
@endphp

<div class="hero-block relative bg-gray-900 text-white py-24"
     @if($backgroundMedia)
         style="background-image: url('{{ $backgroundMedia->url }}'); background-size: cover; background-position: center;"
     @endif>

    @if($backgroundMedia)
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    @endif

    <div class="relative max-w-4xl mx-auto px-4 text-center">
        @if($title)
            <h1 class="text-5xl md:text-6xl font-bold mb-6">{{ $title }}</h1>
        @endif

        @if($subtitle)
            <p class="text-xl md:text-2xl text-gray-200 mb-8 max-w-2xl mx-auto">{{ $subtitle }}</p>
        @endif

        @if($button_text && $button_link)
            <a href="{{ $button_link }}"
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg text-lg transition-colors duration-200">
                {{ $button_text }}
            </a>
        @endif
    </div>
</div>