@props([
    'heading' => null,
    'content' => null,
])

<div class="text-block py-8">
    @if($heading)
        <h2 class="text-3xl font-bold text-gray-900 mb-6">{{ $heading }}</h2>
    @endif

    @if($content)
        <div class="prose prose-lg max-w-none">
            {!! $content !!}
        </div>
    @endif
</div>