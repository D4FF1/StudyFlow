<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'StudyFlow') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <script>
            // Apply saved theme early
            if (localStorage.getItem('studyflow_theme') === 'dark' || (!('studyflow_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="h-full bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100" x-data="{ mobileMenu: false }">
        <div class="min-h-screen">
            <!-- Desktop Sidebar -->
            <aside class="fixed left-0 top-0 z-30 hidden h-screen w-72 flex-col border-r border-slate-200 bg-white p-4 shadow-sm md:flex dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-3 px-2 pb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-violet-500 font-bold text-white shadow-sm">S</div>
                    <div>
                        <div class="text-lg font-bold bg-gradient-to-r from-blue-600 to-violet-600 bg-clip-text text-transparent dark:from-blue-400 dark:to-violet-400">StudyFlow</div>
                        <p class="text-xs text-slate-400">Master your momentum</p>
                    </div>
                </div>

                @php
                    $unreadNotificationsCount = auth()->check() ? auth()->user()->notifications()->whereNull('read_at')->count() : 0;
                @endphp

                <nav class="mt-4 space-y-1.5 overflow-y-auto">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        <svg class="h-5 w-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('tasks.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('tasks.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        <svg class="h-5 w-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Tasks
                    </a>
                    <a href="{{ route('subjects.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('subjects.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        <svg class="h-5 w-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Subjects
                    </a>
                    <a href="{{ route('planner.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('planner.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        <svg class="h-5 w-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Planner
                    </a>
                    <a href="{{ route('focus.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('focus.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        <svg class="h-5 w-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Focus
                    </a>
                    <a href="{{ route('goals.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('goals.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        <svg class="h-5 w-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Goals
                    </a>
                    <a href="{{ route('analytics.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('analytics.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        <svg class="h-5 w-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Analytics
                    </a>
                    <a href="{{ route('notifications.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('notifications.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span>Notifications</span>
                        </div>
                        @if($unreadNotificationsCount > 0)
                            <span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $unreadNotificationsCount }}</span>
                        @endif
                    </a>
                </nav>

                <div class="mt-auto space-y-1.5 border-t border-slate-200 pt-4 dark:border-slate-800">
                    <a href="{{ route('settings.index') }}" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium {{ request()->routeIs('settings.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        <svg class="h-5 w-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Settings
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        <svg class="h-5 w-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm font-medium text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30">
                            <svg class="h-5 w-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Mobile Navigation Drawer -->
            <div x-show="mobileMenu" class="relative z-50 md:hidden" role="dialog" aria-modal="true" style="display: none;">
                <div x-show="mobileMenu" 
                     x-transition:enter="transition-opacity ease-linear duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-linear duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                     @click="mobileMenu = false"></div>

                <div class="fixed inset-0 flex">
                    <div x-show="mobileMenu"
                         x-transition:enter="transition ease-in-out duration-300 transform"
                         x-transition:enter-start="-translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transition ease-in-out duration-300 transform"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="-translate-x-full"
                         class="relative mr-16 flex w-full max-w-xs flex-1 flex-col bg-white p-4 shadow-xl dark:bg-slate-900">
                        
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-violet-500 font-bold text-white">S</div>
                                <div>
                                    <div class="text-lg font-bold">StudyFlow</div>
                                </div>
                            </div>
                            <button @click="mobileMenu = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <nav class="mt-4 space-y-1.5 overflow-y-auto">
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Dashboard</a>
                            <a href="{{ route('tasks.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('tasks.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Tasks</a>
                            <a href="{{ route('subjects.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('subjects.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Subjects</a>
                            <a href="{{ route('planner.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('planner.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Planner</a>
                            <a href="{{ route('focus.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('focus.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Focus</a>
                            <a href="{{ route('goals.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('goals.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Goals</a>
                            <a href="{{ route('analytics.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('analytics.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Analytics</a>
                            <a href="{{ route('notifications.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('notifications.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                                <span>Notifications</span>
                                @if($unreadNotificationsCount > 0)
                                    <span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $unreadNotificationsCount }}</span>
                                @endif
                            </a>
                        </nav>

                        <div class="mt-auto space-y-1.5 border-t border-slate-200 pt-4 dark:border-slate-800">
                            <a href="{{ route('settings.index') }}" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300">Settings</a>
                            <a href="{{ route('profile.edit') }}" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm font-medium text-rose-600 dark:text-rose-400">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="md:pl-72 flex flex-col min-h-screen">
                <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/80 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
                    <div class="flex items-center justify-between px-4 py-3 sm:px-6">
                        <div class="flex items-center gap-3">
                            <button @click="mobileMenu = true" type="button" class="inline-flex md:hidden rounded-lg p-2 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">StudyFlow</p>
                                <h1 class="text-lg font-bold sm:text-xl">{{ ucfirst(str_replace(['.', '-', '_'], ' ', request()->route()?->getName() ?? 'Dashboard')) }}</h1>
                            </div>
                        </div>

                        <!-- Header Global Search & Right Actions -->
                        <div class="flex items-center gap-3">
                            <!-- Search Bar -->
                            <form action="{{ route('search') }}" method="GET" class="relative hidden sm:block w-48 md:w-64">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </span>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search tasks, subjects..." 
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-1.5 pl-9 pr-3 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-900"/>
                            </form>

                            <!-- Notification Icon -->
                            <a href="{{ route('notifications.index') }}" class="relative rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" title="Notifications">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                @if($unreadNotificationsCount > 0)
                                    <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white dark:ring-slate-900"></span>
                                @endif
                            </a>

                            <!-- User Pill -->
                            <div class="flex items-center gap-2 rounded-full bg-slate-100 py-1 pl-1 pr-3 dark:bg-slate-800">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-tr from-indigo-500 to-violet-500 text-xs font-bold text-white">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="text-xs font-medium max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="flex-1 px-4 py-6 sm:px-6 max-w-7xl w-full mx-auto">
                    <!-- StudyFlow Toasts & Alerts -->
                    @if (session('success'))
                        <div x-data="{ show: true }" x-show="show" x-transition class="mb-5 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm text-emerald-800 shadow-sm dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300">
                            <div class="flex items-center gap-2.5">
                                <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ session('success') }}</span>
                            </div>
                            <button @click="show = false" type="button" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 text-sm font-bold ml-3">&times;</button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div x-data="{ show: true }" x-show="show" x-transition class="mb-5 flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm text-rose-800 shadow-sm dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300">
                            <div class="flex items-center gap-2.5">
                                <svg class="h-5 w-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ session('error') }}</span>
                            </div>
                            <button @click="show = false" type="button" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 text-sm font-bold ml-3">&times;</button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
                            <div class="font-semibold mb-1">Please fix the following errors:</div>
                            <ul class="list-disc pl-5 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                    {{ $slot ?? '' }}
                </main>
            </div>
        </div>
    </body>
</html>
