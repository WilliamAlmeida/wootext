<a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}" wire:navigate>
    <x-phosphor-house class="size-5" />
    <span>{{ __('Home') }}</span>
</a>
@auth
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" wire:navigate>
        <x-phosphor-squares-four class="size-5" />
        <span>{{ __('Dashboard') }}</span>
    </a>
@else
    @if (Route::has('login'))
        <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'nav-link-active' : '' }}" wire:navigate>
            <x-phosphor-sign-in class="size-5" />
            <span>{{ __('Log in') }}</span>
        </a>
    @endif
    @if (Route::has('register'))
        <a href="{{ route('register') }}" class="nav-link {{ request()->routeIs('register') ? 'nav-link-active' : '' }}" wire:navigate>
            <x-phosphor-user-plus class="size-5" />
            <span>{{ __('Register') }}</span>
        </a>
    @endif
@endauth
