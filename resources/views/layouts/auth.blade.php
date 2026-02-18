<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full">
    <head>
        @include('partials.head', ['title' => $title ?? null])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100 overflow-x-hidden">
        <div x-data="{ mobileNav: false }" class="min-h-screen flex">
            @if(!env('SIDEBAR_COLLAPSED', false))
                <aside class="hidden lg:flex w-64 flex-col border-r border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                    <div class="h-14 flex items-center px-4 border-b border-zinc-200 dark:border-zinc-800 text-blue-600">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-1 font-semibold" wire:navigate>
                            <x-app-logo class="w-9 h-9" />
                            <span class=" leading-4">Woot<br>Extension</span>
                        </a>
                    </div>

                    <nav class="flex-1 px-3 py-4 text-sm font-medium space-y-1">
                        @include('partials.navigation.auth-links')
                    </nav>

                    <div class="px-3 py-4 text-xs text-zinc-500 dark:text-zinc-400 border-t border-zinc-200 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <x-phosphor-book-open-text class="size-4" />
                            <a href="https://laravel.com/docs" target="_blank" class="hover:text-zinc-700 dark:hover:text-zinc-200">{{ __('Docs') }}</a>
                        </div>
                    </div>
                </aside>
            @endif

            <div class="flex-1 flex flex-col">
                <header class="h-14 flex items-center gap-3 px-4 border-b border-zinc-200 dark:border-zinc-800 bg-white/90 dark:bg-zinc-900/90 backdrop-blur">
                    <button class="lg:hidden btn btn-ghost btn-xs" type="button" @click="mobileNav = true">
                        <x-phosphor-list-bold class="size-5" />
                        <span class="sr-only">{{ __('Open navigation') }}</span>
                    </button>

                    <div class="flex-1"></div>

                    @auth
                    <div class="relative" x-data="{ open: false }">
                        <button class="btn btn-ghost btn-xs shadow-none" type="button" @click="open = ! open" @keydown.escape.window="open = false">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700 text-sm font-semibold">
                                {{ auth()->user()->initials() }}
                            </span>
                            <x-phosphor-caret-down class="size-4" />
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            @click.outside="open = false"
                            class="absolute right-0 mt-2 w-48 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-lg"
                        >
                            <div class="px-3 py-2">
                                <div class="text-sm font-semibold leading-tight">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 leading-tight">{{ auth()->user()->email }}</div>
                            </div>
                            <div class="border-t border-zinc-200 dark:border-zinc-800"></div>
                            <a href="{{ route('settings') }}" class="block px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800" wire:navigate>
                                <span class="inline-flex items-center gap-2"><x-phosphor-gear class="size-4" /> {{ __('Settings') }}</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-zinc-200 dark:border-zinc-800">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 flex items-center gap-2">
                                    <x-phosphor-sign-out class="size-4" />
                                    <span>{{ __('Log Out') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endauth
                </header>

                <main class="flex-1 p-0 lg:p-6">
                    {{ $slot }}
                </main>
            </div>

            <div
                class="lg:hidden fixed inset-0 z-40"
                x-cloak
                x-show="mobileNav"
                @keydown.escape.window="mobileNav = false"
            >
                <div class="absolute inset-0 bg-black/50" @click="mobileNav = false"></div>
                <div class="absolute left-0 top-0 h-full w-64 bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800 shadow-xl p-4 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold" wire:navigate>
                            <x-app-logo class="h-7" />
                            <span class="text-sm tracking-tight">{{ config('app.name') }}</span>
                        </a>
                        <button class="btn btn-ghost btn-sm" type="button" @click="mobileNav = false">
                            <x-phosphor-x-bold class="size-5" />
                        </button>
                    </div>

                    <nav class="flex-1 space-y-1 text-sm font-medium">
                        @include('partials.navigation.auth-links')
                        <a href="{{ route('settings') }}" class="nav-link" wire:navigate>
                            <x-phosphor-gear class="size-5" />
                            <span>{{ __('Settings') }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link w-full">
                                <x-phosphor-sign-out class="size-5" />
                                <span>{{ __('Log Out') }}</span>
                            </button>
                        </form>
                    </nav>
                </div>
            </div>
        </div>

        <livewire:components.notify />

        @livewireScripts
    </body>
</html>
