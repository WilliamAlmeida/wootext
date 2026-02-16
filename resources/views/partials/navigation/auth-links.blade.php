<a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" wire:navigate>
    <x-phosphor-rows class="size-5" />
    <span>{{ __('Dashboard') }}</span>
</a>
<a href="{{ route('connections') }}" class="nav-link {{ request()->routeIs('connections*') ? 'nav-link-active' : '' }}" wire:navigate>
    <x-phosphor-whatsapp-logo class="size-5" />
    <span>{{ __('Conexões') }}</span>
</a>
<a href="{{ route('kanban') }}" class="nav-link {{ request()->routeIs('kanban*') ? 'nav-link-active' : '' }}" wire:navigate>
    <x-phosphor-squares-four class="size-5" />
    <span>{{ __('Kanban') }}</span>
</a>
<a href="{{ route('scheduled-messages') }}" class="nav-link {{ request()->routeIs('scheduled-messages*') ? 'nav-link-active' : '' }}" wire:navigate>
    <x-phosphor-clock class="size-5" />
    <span>{{ __('Mensagens Agendadas') }}</span>
</a>
<!-- <a href="{{ route('settings') }}" class="nav-link {{ request()->routeIs('settings*') ? 'nav-link-active' : '' }}" wire:navigate>
    <x-phosphor-gear class="size-5" />
    <span>{{ __('Settings') }}</span>
</a> -->