@props(['style' => null, 'fixed' => true, 'position' => 'top'])

@php
use STS\FilamentImpersonate\Facades\Impersonation;

// Show banner whenever impersonation is active, regardless of context
$isImpersonating = Impersonation::isImpersonating();

// Debug info (remove in production)
$debugInfo = [
    'isImpersonating' => $isImpersonating,
    'sessionKeys' => collect(session()->all())->keys()->filter(fn($key) => str_contains(strtolower($key), 'imperson'))->values(),
    'currentUser' => auth()->check() ? auth()->user()->email : 'No user',
    'currentGuard' => config('auth.defaults.guard'),
];

$shouldShowBanner = $isImpersonating;

if ($shouldShowBanner) {
    // Get the impersonated user
    $impersonatedUser = auth()->user();
    $display = $impersonatedUser ? $impersonatedUser->name : '(No user found)';

    // Use config values or props
    $style = $style ?? config('filament-impersonate.banner.style', 'dark');
    $fixed = $fixed ?? config('filament-impersonate.banner.fixed', true);
    $position = $position ?? config('filament-impersonate.banner.position', 'top');
    $borderPosition = $position === 'top' ? 'bottom' : 'top';

    $styles = config('filament-impersonate.banner.styles', [
        'light' => [
            'text' => '#1f2937',
            'background' => '#f3f4f6',
            'border' => '#e8eaec',
        ],
        'dark' => [
            'text' => '#f3f4f6',
            'background' => '#1f2937',
            'border' => '#374151',
        ],
    ]);
    $default = $style === 'auto' ? 'light' : $style;
}
@endphp

@if($shouldShowBanner)
<style>
    :root {
        --impersonate-banner-height: 50px;

        --impersonate-light-bg-color: {{ $styles['light']['background'] }};
        --impersonate-light-text-color: {{ $styles['light']['text'] }};
        --impersonate-light-border-color: {{ $styles['light']['border'] }};
        --impersonate-light-button-bg-color: {{ implode(',', sscanf($styles['dark']['background'], "#%02x%02x%02x")) }};
        --impersonate-light-button-text-color: {{ $styles['dark']['text'] }};

        --impersonate-dark-bg-color: {{ $styles['dark']['background'] }};
        --impersonate-dark-text-color: {{ $styles['dark']['text'] }};
        --impersonate-dark-border-color: {{ $styles['dark']['border'] }};
        --impersonate-dark-button-bg-color: {{ implode(',', sscanf($styles['light']['background'], "#%02x%02x%02x")) }};
        --impersonate-dark-button-text-color: {{ $styles['light']['text'] }};
    }

    html {
        margin-{{ $position }}: var(--impersonate-banner-height);
    }

    #impersonate-banner {
        position: {{ $fixed ? 'fixed' : 'absolute' }};
        height: var(--impersonate-banner-height);
        {{ $position }}: 0;
        width: 100%;
        display: flex;
        column-gap: 20px;
        justify-content: center;
        align-items: center;
        background-color: var(--impersonate-{{ $default }}-bg-color);
        color: var(--impersonate-{{ $default }}-text-color);
        border-{{ $borderPosition }}: 1px solid var(--impersonate-{{ $default }}-border-color);
        z-index: 9999;
        font-family: system-ui, sans-serif;
        font-size: 14px;
    }

    @if($style === 'auto')
        .dark #impersonate-banner {
            background-color: var(--impersonate-dark-bg-color);
            color: var(--impersonate-dark-text-color);
            border-{{ $borderPosition }}: 1px solid var(--impersonate-dark-border-color);
        }
    @endif

    #impersonate-banner a {
        display: block;
        padding: 6px 16px;
        border-radius: 4px;
        background-color: rgba(var(--impersonate-{{ $default }}-button-bg-color), 0.8);
        color: var(--impersonate-{{ $default }}-button-text-color);
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.2s;
    }

    @if($style === 'auto')
        .dark #impersonate-banner a {
            background-color: rgba(var(--impersonate-dark-button-bg-color), 0.8);
            color: var(--impersonate-dark-button-text-color);
        }
    @endif

    #impersonate-banner a:hover {
        background-color: rgba(var(--impersonate-{{ $default }}-button-bg-color), 1);
    }

    @if($style === 'auto')
        .dark #impersonate-banner a:hover {
            background-color: rgba(var(--impersonate-dark-button-bg-color), 1);
        }
    @endif

    @media print {
        body {
            margin-top: 0;
        }
        #impersonate-banner {
            display: none;
        }
    }
</style>

<div id="impersonate-banner">
    <div>
        Impersonating <strong>{{ $display }}</strong>
    </div>
    <a href="{{ route('filament-impersonate.leave') }}">Leave impersonation</a>
</div>
@endif

{{-- Debug info (visible in HTML source) --}}
<!-- Impersonation Debug: {{ json_encode($debugInfo) }} -->