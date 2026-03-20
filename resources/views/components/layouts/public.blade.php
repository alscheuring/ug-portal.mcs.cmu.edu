<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? $team->name }} - {{ config('app.name') }}</title>

    @if(isset($page) && $page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @elseif(isset($announcement) && $announcement->excerpt)
        <meta name="description" content="{{ $announcement->excerpt }}">
    @else
        <meta name="description" content="Carnegie Mellon University {{ $team->name }} - Explore our programs, research, and community.">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .eureka-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .eureka-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .eureka-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .eureka-nav-link {
            position: relative;
            transition: color 0.2s ease-in-out;
        }
        .eureka-nav-link:hover::after,
        .eureka-nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            right: 0;
            height: 2px;
            background: #667eea;
        }

        /* Ensure proper prose heading styles */
        .prose h1 {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1.2;
            color: #111827;
            margin-top: 0;
            margin-bottom: 1rem;
        }
        .prose h2 {
            font-size: 1.875rem;
            font-weight: 700;
            line-height: 1.3;
            color: #111827;
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
        .prose h3 {
            font-size: 1.5rem;
            font-weight: 600;
            line-height: 1.4;
            color: #111827;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .prose h4 {
            font-size: 1.25rem;
            font-weight: 600;
            line-height: 1.4;
            color: #111827;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        .prose h5 {
            font-size: 1.125rem;
            font-weight: 600;
            line-height: 1.4;
            color: #111827;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        .prose h6 {
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.4;
            color: #111827;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }

        /* Smooth scroll behavior for anchor links */
        html {
            scroll-behavior: smooth;
        }

        /* Add scroll margin for headings to account for sticky header */
        .prose h2,
        .prose h3,
        .prose h4,
        .prose h5,
        .prose h6 {
            scroll-margin-top: 1rem;
        }
    </style>
</head>
<body class="font-inter antialiased bg-gray-50">
    <!-- Impersonation Banner -->
    <x-impersonation-banner />

    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Top Bar -->
            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                <div class="text-sm text-gray-600">
                    <span class="font-medium text-red-600">Carnegie Mellon University</span> • Mellon College of Science
                </div>
                <div class="flex items-center space-x-4 text-sm">
                    @auth
                        <div class="flex items-center space-x-3">
                            <span class="text-gray-600">Welcome, {{ auth()->user()->name }}</span>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isTeamAdmin())
                                <a href="/admin" class="text-blue-600 hover:text-blue-800 font-medium">Admin Panel</a>
                            @endif
                            <form method="POST" action="{{ route('safe-logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-gray-800">Logout</button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-medium">Login</a>
                    @endauth
                </div>
            </div>

            <!-- Main Navigation -->
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-6">
                    <!-- Team Logo/Brand -->
                    <a href="{{ route('public.team.index', $team->slug) }}" class="flex items-center space-x-4">
                        <img src="{{ asset('images/mcs-logo.jpg') }}"
                             alt="Mellon College of Science Logo"
                             class="w-12 h-12 rounded-full object-cover shadow-md">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ config('app.name') }}</h1>
                            <p class="text-sm text-gray-600">{{ $team->name }}</p>
                        </div>
                    </a>
                </div>

                <!-- Primary Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('public.team.index', $team->slug) }}"
                       class="eureka-nav-link text-gray-700 hover:text-gray-900 font-medium @if(request()->routeIs('public.team.index')) active @endif">
                        Home
                    </a>

                    @foreach($navigation ?? [] as $navItem)
                        @if($navItem['type'] === 'divider')
                            <span class="text-gray-400">|</span>
                        @elseif($navItem['type'] === 'parent' && isset($navItem['children']) && count($navItem['children']) > 0)
                            <!-- Dropdown Parent -->
                            <div class="relative group">
                                <button class="eureka-nav-link text-gray-700 hover:text-gray-900 font-medium flex items-center {{ $navItem['css_class'] ?? '' }}"
                                        @if($navItem['description'] ?? '') title="{{ $navItem['description'] }}" @endif>
                                    @if($navItem['icon'] ?? '')
                                        <i class="{{ $navItem['icon'] }} mr-1"></i>
                                    @endif
                                    {{ $navItem['title'] }}
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <!-- Dropdown Menu -->
                                <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                    <div class="py-1">
                                        @foreach($navItem['children'] as $childItem)
                                            @if($childItem['url'])
                                                <a href="{{ $childItem['url'] }}"
                                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                                   @if($childItem['opens_in_new_tab'] ?? false) target="_blank" rel="noopener" @endif
                                                   @if($childItem['description'] ?? '') title="{{ $childItem['description'] }}" @endif>
                                                    @if($childItem['icon'] ?? '')
                                                        <i class="{{ $childItem['icon'] }} mr-2"></i>
                                                    @endif
                                                    {{ $childItem['title'] }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @elseif($navItem['url'])
                            @php
                                $isActive = false;
                                // Check for announcements/polls active states based on current route
                                if ($navItem['type'] === 'announcements' && request()->routeIs('public.team.announcements.*')) {
                                    $isActive = true;
                                } elseif ($navItem['type'] === 'polls' && request()->routeIs('public.team.polls.*')) {
                                    $isActive = true;
                                } elseif (request()->url() === $navItem['url']) {
                                    $isActive = true;
                                }
                            @endphp
                            <a href="{{ $navItem['url'] }}"
                               class="eureka-nav-link text-gray-700 hover:text-gray-900 font-medium {{ $navItem['css_class'] ?? '' }} @if($isActive) active @endif"
                               @if($navItem['opens_in_new_tab'] ?? false) target="_blank" rel="noopener" @endif
                               @if($navItem['description'] ?? '') title="{{ $navItem['description'] }}" @endif>
                                @if($navItem['icon'] ?? '')
                                    <i class="{{ $navItem['icon'] }} mr-1"></i>
                                @endif
                                {{ $navItem['title'] }}
                            </a>
                        @endif
                    @endforeach
                </nav>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-500 hover:text-gray-600" onclick="toggleMobileMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="md:hidden hidden border-t border-gray-100 py-4">
                <nav class="space-y-3">
                    <a href="{{ route('public.team.index', $team->slug) }}" class="block text-gray-700 hover:text-gray-900 font-medium">Home</a>

                    @foreach($navigation ?? [] as $navItem)
                        @if($navItem['type'] === 'divider')
                            <hr class="border-gray-200">
                        @elseif($navItem['type'] === 'parent' && isset($navItem['children']) && count($navItem['children']) > 0)
                            <!-- Mobile Parent Menu -->
                            <div class="py-2">
                                <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider px-2">
                                    @if($navItem['icon'] ?? '')
                                        <i class="{{ $navItem['icon'] }} mr-2"></i>
                                    @endif
                                    {{ $navItem['title'] }}
                                </div>
                                <div class="mt-2 ml-4 space-y-2">
                                    @foreach($navItem['children'] as $childItem)
                                        @if($childItem['url'])
                                            <a href="{{ $childItem['url'] }}"
                                               class="block text-gray-700 hover:text-gray-900 font-medium text-sm {{ $childItem['css_class'] ?? '' }}"
                                               @if($childItem['opens_in_new_tab'] ?? false) target="_blank" rel="noopener" @endif>
                                                @if($childItem['icon'] ?? '')
                                                    <i class="{{ $childItem['icon'] }} mr-2"></i>
                                                @endif
                                                {{ $childItem['title'] }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @elseif($navItem['url'])
                            <a href="{{ $navItem['url'] }}"
                               class="block text-gray-700 hover:text-gray-900 font-medium {{ $navItem['css_class'] ?? '' }}"
                               @if($navItem['opens_in_new_tab'] ?? false) target="_blank" rel="noopener" @endif>
                                @if($navItem['icon'] ?? '')
                                    <i class="{{ $navItem['icon'] }} mr-2"></i>
                                @endif
                                {{ $navItem['title'] }}
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    <!-- Breadcrumbs -->
    @if(isset($breadcrumbs) && count($breadcrumbs) > 1)
        <div class="bg-gray-50 border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm">
                        @foreach($breadcrumbs as $index => $crumb)
                            <li class="flex items-center">
                                @if($index > 0)
                                    <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                                @if($index === count($breadcrumbs) - 1)
                                    <span class="text-gray-500 font-medium">{{ $crumb['title'] }}</span>
                                @else
                                    <a href="{{ $crumb['url'] }}" class="text-blue-600 hover:text-blue-800">{{ $crumb['title'] }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Team Info -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="{{ asset('images/mcs-logo.jpg') }}"
                             alt="Mellon College of Science Logo"
                             class="w-10 h-10 rounded-full object-cover">
                        <h3 class="text-lg font-bold">{{ $team->name }}</h3>
                    </div>
                    <p class="text-gray-300 mb-4">
                        Carnegie Mellon University<br>
                        Mellon College of Science<br>
                        Pittsburgh, PA 15213
                    </p>
                    @if($team->manager_email)
                        <p class="text-gray-300">
                            <strong>Contact:</strong>
                            <a href="mailto:{{ $team->manager_email }}" class="text-blue-400 hover:text-blue-300">{{ $team->manager_email }}</a>
                        </p>
                    @endif
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="{{ route('public.team.index', $team->slug) }}" class="hover:text-white">Home</a></li>
                        <li><a href="{{ route('public.team.announcements.index', $team->slug) }}" class="hover:text-white">News</a></li>
                        <li><a href="{{ route('public.team.polls.index', $team->slug) }}" class="hover:text-white">Polls</a></li>
                        @foreach(($navigation ?? []) as $navItem)
                            @if($navItem['type'] !== 'divider' && $navItem['url'])
                                <li>
                                    <a href="{{ $navItem['url'] }}"
                                       class="hover:text-white {{ $navItem['css_class'] ?? '' }}"
                                       @if($navItem['opens_in_new_tab'] ?? false) target="_blank" rel="noopener" @endif>
                                        @if($navItem['icon'] ?? '')
                                            <i class="{{ $navItem['icon'] }} mr-2"></i>
                                        @endif
                                        {{ $navItem['title'] }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <!-- University Links -->
                <div>
                    <h4 class="font-semibold mb-4">Carnegie Mellon</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="https://www.cmu.edu" class="hover:text-white">CMU Homepage</a></li>
                        <li><a href="https://www.cmu.edu/mcs" class="hover:text-white">Mellon College of Science</a></li>
                        <li><a href="https://www.cmu.edu/directory" class="hover:text-white">Directory</a></li>
                        <li><a href="https://www.cmu.edu/visit" class="hover:text-white">Visit CMU</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Carnegie Mellon University. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }
    </script>

    @stack('scripts')
</body>
</html>