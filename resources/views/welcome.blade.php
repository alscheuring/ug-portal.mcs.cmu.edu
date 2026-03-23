<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CMU UG Portal') }} - Mellon College of Science</title>
    <meta name="description" content="Carnegie Mellon University Undergraduate Portal for the Mellon College of Science. Access resources for Biological Sciences, Mathematical Sciences, Chemistry, and Physics departments.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .department-icon {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }
    </style>
</head>
<body class="font-inter antialiased bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <!-- CMU Logo Placeholder -->
                    <div class="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7V10C2 16 12 22 12 22S22 16 22 10V7L12 2Z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">CMU UG Portal</h1>
                        <p class="text-sm text-gray-600">Mellon College of Science</p>
                    </div>
                </div>

                <!-- Login Button -->
                @guest
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('auth.google.redirect') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Sign In
                        </a>
                    </div>
                @else
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Logout</button>
                        </form>
                    </div>
                @endguest
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="gradient-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                Welcome to the Undergraduate Portal
            </h2>
            <p class="text-xl text-red-100 mb-8 max-w-3xl mx-auto">
                Your gateway to academic resources, course information, and department-specific tools
                for the Mellon College of Science at Carnegie Mellon University.
            </p>

            @if (session('error'))
                <div class="max-w-md mx-auto mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('info'))
                <div class="max-w-md mx-auto mb-6 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
                    {{ session('info') }}
                </div>
            @endif

            @guest
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('auth.google.redirect') }}"
                       class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-lg font-medium rounded-md text-red-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Sign in with Andrew ID
                    </a>
                </div>

                @if (config('app.debug') && config('app.env') !== 'production')
                    <div class="mt-6">
                        <p class="text-red-200 text-sm mb-2">Development Only</p>
                        <a href="{{ route('auth.test-login') }}"
                           class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-100 hover:bg-red-600 transition-colors">
                            Test Login ({{ env('ANDREW_TEST_USER', 'Not Configured') }})
                        </a>
                    </div>
                @endif

                <p class="mt-8 text-sm text-red-200">
                    Access restricted to CMU Andrew accounts only
                </p>
            @else
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 max-w-md mx-auto">
                    <p class="text-white text-lg mb-4">Welcome back, {{ auth()->user()->name }}!</p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isTeamAdmin())
                            <a href="/admin" class="px-6 py-3 bg-white text-red-600 rounded-md font-medium hover:bg-gray-50 transition-colors">
                                Admin Panel
                            </a>
                        @endif
                        <a href="{{ auth()->user()->getRedirectUrl() }}" class="px-6 py-3 bg-red-700 text-white rounded-md font-medium hover:bg-red-800 transition-colors">
                            {{ auth()->user()->isStudent() && auth()->user()->roles->count() === 1 && auth()->user()->team ? auth()->user()->team->name . ' Portal' : 'Student Portal' }}
                        </a>
                    </div>
                </div>
            @endguest
        </div>
    </section>

    <!-- Departments Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Explore Our Departments</h3>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Discover resources, courses, and opportunities across the four departments
                    of the Mellon College of Science.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Biological Sciences -->
                <a href="/biosci" class="card-hover bg-white rounded-xl shadow-lg p-8 text-center border border-gray-100">
                    <div class="department-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Biological Sciences</h4>
                    <p class="text-gray-600 text-sm mb-4">Explore life at all levels, from molecules to ecosystems</p>
                    <div class="text-red-600 font-medium text-sm">Visit Department →</div>
                </a>

                <!-- Mathematical Sciences -->
                <a href="/math" class="card-hover bg-white rounded-xl shadow-lg p-8 text-center border border-gray-100">
                    <div class="department-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Mathematical Sciences</h4>
                    <p class="text-gray-600 text-sm mb-4">Pure and applied mathematics, statistics, and operations research</p>
                    <div class="text-red-600 font-medium text-sm">Visit Department →</div>
                </a>

                <!-- Chemistry -->
                <a href="/chemistry" class="card-hover bg-white rounded-xl shadow-lg p-8 text-center border border-gray-100">
                    <div class="department-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Chemistry</h4>
                    <p class="text-gray-600 text-sm mb-4">Understanding matter at the molecular and atomic level</p>
                    <div class="text-red-600 font-medium text-sm">Visit Department →</div>
                </a>

                <!-- Physics -->
                <a href="/physics" class="card-hover bg-white rounded-xl shadow-lg p-8 text-center border border-gray-100">
                    <div class="department-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Physics</h4>
                    <p class="text-gray-600 text-sm mb-4">From quantum mechanics to astrophysics and beyond</p>
                    <div class="text-red-600 font-medium text-sm">Visit Department →</div>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Portal Features</h3>
                <p class="text-lg text-gray-600">Everything you need for academic success</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg p-8 shadow-md">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Course Resources</h4>
                    <p class="text-gray-600">Access syllabi, assignments, and course materials from your department</p>
                </div>

                <div class="bg-white rounded-lg p-8 shadow-md">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Faculty Directory</h4>
                    <p class="text-gray-600">Connect with professors, advisors, and department staff</p>
                </div>

                <div class="bg-white rounded-lg p-8 shadow-md">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a1 1 0 01-1-1V9a1 1 0 011-1h1a2 2 0 100-4H4a1 1 0 01-1-1V4a1 1 0 011-1h3a1 1 0 001-1z"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Research Opportunities</h4>
                    <p class="text-gray-600">Discover undergraduate research programs and internships</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 7V10C2 16 12 22 12 22S22 16 22 10V7L12 2Z"/>
                            </svg>
                        </div>
                        <span class="text-lg font-semibold">CMU UG Portal</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Mellon College of Science<br>
                        Carnegie Mellon University<br>
                        Pittsburgh, PA 15213
                    </p>
                </div>

                <div>
                    <h5 class="font-semibold mb-4">Quick Links</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Academic Calendar</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Student Handbook</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Course Catalog</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Advising</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="font-semibold mb-4">Support</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Help Desk</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Technical Support</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Feedback</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Carnegie Mellon University. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>


        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif

        <!-- Impersonation Banner -->
        <x-impersonate-banner/>
    </body>
</html>
