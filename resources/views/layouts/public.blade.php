<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full">
    <head>
        @include('partials.head', ['title' => $title ?? null])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-white text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
        <div x-data="{ mobileNav: false }" class="min-h-screen flex">
            <aside class="hidden lg:flex w-60 flex-col border-r border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                <div class="h-14 flex items-center px-4 border-b border-zinc-200 dark:border-zinc-800">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 font-semibold" wire:navigate>
                        <!-- <x-app-logo class="h-7" /> -->
                        <span class="text-sm tracking-tight">{{ config('app.name') }}</span>
                    </a>
                </div>

                <nav class="flex-1 px-3 py-4 text-sm font-medium space-y-1">
                    @include('partials.navigation.public-links')
                </nav>

                <div class="px-3 py-4 text-xs text-zinc-500 dark:text-zinc-400 border-t border-zinc-200 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <x-phosphor-chat-circle-dots class="size-4" />
                        <span>{{ __('Support-ready layout') }}</span>
                    </div>
                </div>
            </aside>

            <div class="flex-1 flex flex-col">
                <header class="h-14 flex items-center gap-3 px-4 border-b border-zinc-200 dark:border-zinc-800 bg-white/90 dark:bg-zinc-900/90 backdrop-blur">
                    <button class="lg:hidden btn btn-ghost btn-sm" type="button" @click="mobileNav = true">
                        <x-phosphor-list-bold class="size-5" />
                        <span class="sr-only">{{ __('Open navigation') }}</span>
                    </button>

                    <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold lg:hidden" wire:navigate>
                        <x-app-logo class="h-6" />
                        <span class="text-sm tracking-tight">{{ config('app.name') }}</span>
                    </a>

                    <div class="flex-1"></div>

                    <div class="flex items-center gap-2 text-sm">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm" wire:navigate>{{ __('Dashboard') }}</a>
                        @else
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="btn btn-ghost btn-sm" wire:navigate>{{ __('Log in') }}</a>
                            @endif
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-primary btn-sm" wire:navigate>{{ __('Create account') }}</a>
                            @endif
                        @endauth
                    </div>
                </header>

                <main class="flex-1 p-4 lg:p-8">
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
                        <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold" wire:navigate>
                            <x-app-logo class="h-7" />
                            <span class="text-sm tracking-tight">{{ config('app.name') }}</span>
                        </a>
                        <button class="btn btn-ghost btn-sm" type="button" @click="mobileNav = false">
                            <x-phosphor-x-bold class="size-5" />
                        </button>
                    </div>

                    <nav class="flex-1 space-y-1 text-sm font-medium">
                        @include('partials.navigation.public-links')
                    </nav>
                </div>
            </div>
        </div>

        <livewire:components.notify />

        @livewireScripts
    </body>
</html>
