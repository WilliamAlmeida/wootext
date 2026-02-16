<div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-x-auto max-w-screen">
    <div class="flex items-center gap-3">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Kanban</h1>

        {{-- Funnel Tabs --}}
        <div class="flex items-center gap-1 ml-4">
            @foreach ($this->funnels as $funnel)
                <button
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg transition-colors cursor-pointer text-nowrap {{ $activeFunnelId === $funnel->id ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
                    wire:click="selectFunnel({{ $funnel->id }})" wire:target="selectFunnel"
                    wire:loading.class="cursor-wait opacity-70" wire:loading.attr="disabled"
                >
                    <span class="inline-block w-2 h-2 rounded-full mr-1.5" style="background-color: {{ $funnel->color }}"></span>
                    {{ $funnel->name }}
                    @if ($funnel->is_system)
                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">Sistema</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <div class="flex items-center gap-2">
        <div class="relative min-w-[200px]">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <x-phosphor-magnifying-glass class="h-4 w-4 text-gray-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                class="block w-full py-1 pl-10 sm:text-sm border-gray-300 dark:border-zinc-700 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-zinc-100 placeholder-zinc-400"
                placeholder="Buscar cards..."
            >
        </div>

        <button class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors" wire:click="$dispatch('open-stage-modal', { funnelId: @js($activeFunnelId) })">
            <x-phosphor-plus class="w-4 h-4 mr-1" />
            Etapa
        </button>
        <button class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors" wire:click="$dispatch('open-funnel-modal')">
            <x-phosphor-plus class="w-4 h-4 mr-1" />
            Funil
        </button>
        <button class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors" wire:click.async="refreshBoardData">
            <x-phosphor-arrows-clockwise class="w-4 h-4 mr-1" wire:loading.class="animate-spin" wire:target="refreshBoardData" />
            Atualizar
        </button>


        @if ($this->activeFunnel && ! $this->activeFunnel->is_system)
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="inline-flex items-center justify-center px-2 py-1.5 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors">
                    <x-phosphor-dots-three-vertical class="w-4 h-4" />
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-zinc-800 ring-1 ring-black ring-opacity-5 z-50">
                    <ul class="py-1">
                        <li>
                                <button wire:click="$dispatch('open-funnel-modal', { funnelId: {{ $activeFunnelId }} })" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700">
                                    <x-phosphor-pencil class="w-4 h-4 mr-2" />
                                    Editar Funil
                                </button>
                        </li>
                        <li>
                            <button wire:click="deleteFunnel({{ $activeFunnelId }})" wire:confirm="Tem certeza que deseja excluir este funil?" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                <x-phosphor-trash class="w-4 h-4 mr-2" />
                                Excluir Funil
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>