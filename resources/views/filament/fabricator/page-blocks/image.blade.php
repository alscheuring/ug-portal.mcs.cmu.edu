@props([
    'image' => null,
    'alt_text' => null,
    'caption' => null,
    'alignment' => 'center',
    'size' => 'medium',
])

@php
    $alignmentClass = match($alignment) {
        'left' => 'text-left',
        'right' => 'text-right',
        'full' => 'w-full',
        default => 'text-center',
    };

    $sizeClass = match($size) {
        'small' => 'max-w-sm',
        'medium' => 'max-w-2xl',
        'large' => 'max-w-4xl',
        'original' => '',
        default => 'max-w-2xl',
    };

    $containerClass = $alignment === 'full' ? 'w-full' : '';
@endphp

<div class="image-block py-8">
    @if($containerClass)
        <div class="{{ $containerClass }}">
    @endif
        <div class="{{ $alignmentClass }}">
            @if($image)
                <div class="{{ $alignment === 'full' ? '' : 'inline-block' }} {{ $sizeClass }}">
                    @php
                        $media = \Awcodes\Curator\Models\Media::find($image);
                    @endphp
                    <img src="{{ $media ? $media->url : '' }}"
                         alt="{{ $alt_text }}"
                         class="w-full h-auto rounded-lg shadow-lg">

                    @if($caption)
                        <p class="text-gray-600 text-sm mt-2 italic">{{ $caption }}</p>
                    @endif
                </div>
            @endif
        </div>
    @if($containerClass)
        </div>
    @endif
</div>