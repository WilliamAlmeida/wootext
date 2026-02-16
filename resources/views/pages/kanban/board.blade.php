<div class="flex flex-col h-full">
    <livewire:components.kanban.card-detail lazy />
    <livewire:components.kanban.board-modals lazy />

    @island(name: 'kanban-board', lazy: true, always: true)
        @placeholder
            <div class="flex items-center justify-center h-full">
                <x-phosphor-spinner-gap class="animate-spin h-8 w-8 text-gray-500" />
            </div>
        @endplaceholder

        <div wire:poll.15s class="flex flex-col h-full">
            {{-- Toolbar --}}
            @include('pages.kanban.toolbar')

            {{-- Board --}}
            <div class="flex-1 overflow-x-auto overflow-y-hidden py-4">
                @if ($this->activeFunnel)
                    <div class="flex gap-4 h-full max-w-screen sm:max-w-[calc(100vw-20rem)]">
                        @foreach ($this->stages as $key => $stage)
                            <div class="flex flex-col w-80 min-w-[320px] bg-zinc-100 dark:bg-zinc-800 rounded-xl shrink-0">
                                {{-- Column Header --}}
                                <div class="flex items-center justify-between px-3 py-2.5 border-b border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: {{ $stage->color }}"></span>
                                        <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100">{{ $stage->name }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">{{ count($this->stageCards[$stage->id] ?? []) }}</span>
                                    </div>
                                    <div x-data="{ open: false }" class="relative" x-cloak>
                                        <button @click="open = !open" class="inline-flex items-center justify-center p-1 text-sm font-medium rounded-lg bg-transparent text-gray-700 hover:bg-gray-200 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors">
                                            <x-phosphor-dots-three-vertical class="w-4 h-4" />
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-zinc-800 ring-1 ring-gray-300 ring-opacity-5 z-50">
                                            <ul class="py-1">
                                                <li>
                                                    <button wire:click="$dispatch('open-stage-modal', { stageId: {{ $stage->id }} })" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700">
                                                        <x-phosphor-pencil class="w-4 h-4 mr-2" />
                                                        Editar
                                                    </button>
                                                </li>
                                                <li>
                                                    <button wire:click="deleteStage({{ $stage->id }})" wire:confirm="Excluir etapa?" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                        <x-phosphor-trash class="w-4 h-4 mr-2" />
                                                        Excluir
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Cards List (sortable) --}}
                                <div
                                    class="flex-1 overflow-y-auto p-2 space-y-2 max-h-[calc(100vh-240px)] sm:max-h-[73vh]"
                                    wire:sort="handleSort"
                                    wire:sort:group="cards"
                                    wire:sort:group-id="{{ $stage->id }}"
                                >
                                    @foreach (($this->stageCards[$stage->id] ?? []) as $card)
                                        <div
                                            wire:sort:item="{{ $card['id'] }}"
                                            wire:key="card-{{ $card['id'] }}"
                                            class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm hover:shadow-md transition-shadow cursor-grab active:cursor-grabbing"
                                        >
                                            <div class="p-3">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1 min-w-0">
                                                        <img src="{{ $card['image_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($card['contact_name'][0] ?? 'Contato') . '&background=random&color=fff&size=64' }}" alt="{{ $card['contact_name'] ?? 'Contato' }}" class="w-8 h-8 rounded-full object-cover mr-2"
                                                            onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($card['contact_name'][0] ?? 'Contato') }}&background=random&color=fff&size=64';"
                                                        >

                                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                                            {{ $card['display_name'] ?? $card['custom_name'] ?? 'Conversa #' . $card['conversation_id'] }}
                                                        </p>
                                                        @if ($card['phone_number'])
                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                                                {{ $card['phone_number'] }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    @if($card['unread_count'] > 0)
                                                        <span class="mt-0.5 text-white bg-red-500 rounded-lg w-5 h-5 text-xs flex items-center justify-center mr-1">{{ $card['unread_count'] }}</span>
                                                    @endif
                                                    <div x-data="{ open: false }" class="relative" x-cloak>
                                                        <button @click="open = !open" class="inline-flex items-center justify-center p-0.5 text-sm font-medium rounded-lg bg-transparent text-gray-700 hover:bg-gray-200 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors">
                                                            <x-phosphor-dots-three-vertical class="w-4 h-4" />
                                                        </button>
                                                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-zinc-800 ring-1 ring-gray-300 ring-opacity-5 z-50">
                                                            <ul class="py-1">
                                                                <li>
                                                                    <button wire:click="openCardDetail({{ $card['conversation_id'] }})" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700">
                                                                        <x-phosphor-eye class="w-4 h-4 mr-2" />
                                                                        Detalhes
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <button wire:click="$dispatch('open-schedule-modal', { conversationId: {{ $card['conversation_id'] }} })" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700">
                                                                        <x-phosphor-clock class="w-4 h-4 mr-2" />
                                                                        Agendar Mensagem
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <a href="{{ str(config('services.chatwoot.url'))->replace('https', 'http') }}/app/accounts/{{ $this->activeFunnel['account_id'] }}/inbox/{{ $card['inbox_id'] }}/conversations/{{ $card['conversation_id'] }}" target="_blank" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700">
                                                                        <x-phosphor-chat-circle-dots class="w-4 h-4 mr-2"/>
                                                                        Ver Conversa
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <hr class="my-1 border-t border-zinc-200 dark:border-zinc-700">
                                                                </li>
                                                                <li>
                                                                    <button wire:click="deleteCard({{ $card['id'] }})" wire:confirm="Excluir card?" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                                        <x-phosphor-trash class="w-4 h-4 mr-2" />
                                                                        Excluir
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                                        #{{ $card['conversation_id'] }}
                                                    </span>
                                                    @if($card['priority'])
                                                        <span @class([
                                                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                                            'bg-green-100 text-green-700 dark:bg-green-700 dark:text-green-300' => $card['priority'] === 'urgent',
                                                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-700 dark:text-yellow-300' => $card['priority'] === 'high',
                                                            'bg-red-100 text-red-700 dark:bg-red-700 dark:text-red-300' => $card['priority'] === 'medium',
                                                            'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-zinc-300' 
                                                        ])>
                                                            {{ $card['priority'] }}
                                                        </span>
                                                    @endif
                                                    @if($card['assignee'])
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                                            {{ $card['assignee'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <x-phosphor-columns class="w-12 h-12 text-zinc-400 mx-auto mb-3" />
                            <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Nenhum funil encontrado</h2>
                            <p class="mt-1 text-zinc-600 dark:text-zinc-400">Crie um funil para começar a organizar suas conversas.</p>
                            <button class="inline-flex items-center px-4 py-2 mt-4 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors" wire:click="$dispatch('open-funnel-modal')">
                                Criar Funil
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Card Detail Side Panel --}}
        </div>
    @endisland
</div>