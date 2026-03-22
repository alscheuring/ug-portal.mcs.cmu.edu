@php
    $vis = \Crumbls\Layup\View\BaseView::visibilityClasses($data['hide_on'] ?? []);
    $cols = $data['columns'] ?? 3;
    $gap = $data['gap'] ?? '0.5rem';
    $rounded = !empty($data['rounded']);
@endphp
<div @if(!empty($data['id']))id="{{ $data['id'] }}"@endif
     class="{{ $vis }} {{ $data['class'] ?? '' }}"
     style="columns: {{ $cols }}; column-gap: {{ $gap }}; {{ \Crumbls\Layup\View\BaseView::buildInlineStyles($data) }}"
     {!! \Crumbls\Layup\View\BaseView::animationAttributes($data) !!}
>
    @foreach(($data['images'] ?? []) as $image)
        @php
            $imgSrc = is_array($image) ? ($image['src'] ?? $image['image'] ?? $image['url'] ?? '') : $image;
            $imgAlt = is_array($image) ? ($image['alt'] ?? '') : '';
            $imgUrl = (!empty($imgSrc) && str_starts_with($imgSrc, 'http')) ? $imgSrc : (!empty($imgSrc) ? asset('storage/' . $imgSrc) : '');
        @endphp
        @if(!empty($imgSrc))
            <img src="{{ $imgUrl }}" alt="{{ $imgAlt }}" loading="lazy"
                 class="w-full mb-[{{ $gap }}] {{ $rounded ? 'rounded-lg' : '' }} hover:opacity-90 transition-opacity"
                 style="break-inside: avoid" />
        @endif
    @endforeach
</div>
