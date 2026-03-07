<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Mensagens Agendadas</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Gerencie suas mensagens agendadas para enviar no momento certo.</p>
        </div>
        <button type="button" wire:click="create" class="btn btn-primary">
            <x-phosphor-plus class="size-4" />
            Agendar Mensagem
        </button>
    </div>

    {{-- List Messages --}}
    <div class="card-woot overflow-clip mt-6">
        <table class="table w-full border-collapse">
            <thead>
                <tr class="*:text-left *:font-medium *:p-2 bg-gray-50">
                    <th>#</th>
                    <th>Conversa</th>
                    <th>Mensagem</th>
                    <th>Agendada Para</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->getScheduledMessages as $scheduled)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-700/50 transition-colors *:px-2 *:py-2 *:text-nowrap">
                        <td>{{ $loop->index+1 }}</td>
                        <td>
                            <span class="text-sm text-gray-500 flex gap-x-1 items-center">
                                <x-phosphor-chat-teardrop-dots class="w-2 h-2" />
                                {{ 'Conversa #' . $scheduled->card?->conversation_id }}
                            </span>
                        </td>
                        <td class="flex gap-2 text-sm text-zinc-900 dark:text-zinc-100 !text-wrap max-w-52">
                            @php($scheduledAttachments = $this->normalizeAttachments($scheduled->attachments))
                            @if(! empty($scheduledAttachments[0]['filePath']))
                                <a href="{{ Storage::url($scheduledAttachments[0]['filePath']) }}" target="_blank" class="text-sm text-blue-500 hover:underline" title="{{ $scheduledAttachments[0]['originalName'] ?? 'Ver Anexo' }}">
                                    <x-phosphor-paperclip class="w-3 h-3 inline-block ml-1" />
                                </a>
                            @endif
                            <span class="truncate">{{ $scheduled->message }}</span>
                        </td>
                        <td>
                            <span class="text-sm text-gray-500 flex gap-x-1 items-center">
                                <x-phosphor-calendar class="w-2 h-2" />
                                {{ $scheduled->scheduled_at->format('d/m/Y H:i') }}
                            </span>
                        </td>
                        <td>
                            @switch($scheduled->status)
                                @case('pending')
                                    <span class="text-xs text-yellow-500 flex gap-x-1 items-center">
                                        <x-phosphor-hourglass-fill class="w-2 h-2" />
                                        Pendente
                                    </span>
                                    @break
                                @case('sent')
                                    <span class="text-xs text-green-500 flex gap-x-1 items-center">
                                        <x-phosphor-check class="w-2 h-2" />
                                        Enviada
                                    </span>
                                    @break
                                @case('error')
                                    <span class="text-xs text-red-500 flex gap-x-1 items-center">
                                        <x-phosphor-x class="w-2 h-2" />
                                        Erro
                                    </span>
                                    @break
                                @default
                                    <span class="text-xs text-gray-500 flex gap-x-1 items-center">
                                        Desconecido
                                    </span>
                            @endswitch
                        </td>
                        <td class="space-x-2">
                            @if($scheduled->status === 'pending')
                                <button class="text-sm text-blue-500 hover:underline cursor-pointer" wire:click="edit({{ $scheduled->id }})">Editar</button>
                                <button class="text-sm text-red-500 hover:underline cursor-pointer" wire:click="delete({{ $scheduled->id }})" wire:confirm="Tem certeza que deseja deletar esta mensagem agendada?">Deletar</button>
                            @else
                                <span class="text-xs text-gray-500 flex gap-x-1 items-center">
                                    <x-phosphor-clock class="w-2 h-2" />
                                    {{ $scheduled->updated_at->diffForHumans() }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-sm text-zinc-500 dark:text-zinc-400">Nenhuma mensagem agendada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="max-w-[calc(60rem-3px)] mx-auto">
        {{ $this->getScheduledMessages->links() }}
    </div>

    <x-modal model="showScheduleModal" title="{{ $editingMessageId ? 'Editar Mensagem' : 'Agendar Mensagem' }}" maxWidth="md">
        <div class="space-y-4">
            <div class="space-y-1">
                @if($card)
                    <h5 class="text-sm text-gray-500 dark:text-zinc-400">
                        {{ $card?->custom_name }}
                        {{ 'Conversa #' . $card?->conversation_id }}
                    </h5>
                @else
                    <div
                        x-data="searchableConversationSelect()"
                        x-on:click.outside="closeList()"
                        x-on:keydown.escape.prevent.stop="closeList()"
                        class="relative"
                    >
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Conversa</label>
                        <div class="relative">
                            <input
                                x-ref="searchInput"
                                type="text"
                                wire:model.live.debounce.300ms="filterCard"
                                x-on:focus="openList()"
                                x-on:input="handleInput()"
                                x-on:keydown.arrow-down.prevent="moveHighlight(1)"
                                x-on:keydown.arrow-up.prevent="moveHighlight(-1)"
                                x-on:keydown.enter.prevent="selectHighlighted()"
                                placeholder="Digite nome, telefone ou ID da conversa..."
                                autocomplete="off"
                                role="combobox"
                                aria-autocomplete="list"
                                aria-controls="scheduled-message-conversations"
                                :aria-expanded="open.toString()"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 pr-10 text-zinc-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                            />
                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 dark:text-zinc-500">
                                <x-phosphor-magnifying-glass class="size-4" />
                            </span>
                        </div>

                        <div
                            x-cloak
                            x-show="open"
                            x-transition.opacity.duration.100ms
                            id="scheduled-message-conversations"
                            class="absolute z-20 mt-2 max-h-64 w-full overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                        >
                            <div x-ref="options" class="max-h-64 overflow-y-auto py-1">
                                @forelse($this->availableCards as $c)
                                    @php($conversationName = $c['custom_name'] ?: $c['phone_number'])
                                    @php($conversationLabel = $conversationName . ' (Conversa #' . $c['conversation_id'] . ')')
                                    <button
                                        type="button"
                                        wire:key="conversation-option-{{ $c['id'] ?? $c['conversation_id'] }}"
                                        data-option
                                        x-on:mouseenter="highlightedIndex = {{ $loop->index }}"
                                        x-on:click="selectOption({{ (int) $c['conversation_id'] }}, @js($conversationLabel))"
                                        :class="highlightedIndex === {{ $loop->index }} ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200' : 'text-zinc-700 dark:text-zinc-200'"
                                        class="flex w-full items-start justify-between gap-3 px-3 py-2 text-left transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                    >
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-medium">{{ $conversationName }}</span>
                                            <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $c['phone_number'] }} · Conversa #{{ $c['conversation_id'] }}</span>
                                        </span>
                                        <x-phosphor-arrow-right class="mt-0.5 size-4 shrink-0 text-zinc-300 dark:text-zinc-600" />
                                    </button>
                                @empty
                                    <div class="px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        Nenhuma conversa encontrada para esse filtro.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        @if($selectedConversationId)
                            <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                Conversa selecionada: #{{ $selectedConversationId }}
                            </p>
                        @endif

                        @error('selectedConversationId') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Mensagem</label>
                <textarea wire:model="content" placeholder="Digite sua mensagem" maxlength="1000" rows="6" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                @error('content') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Data e Hora</label>
                <input type="datetime-local" wire:model="datetime" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                @error('datetime') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Anexo</label>
                <input type="file" wire:model="attachment" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" />
                @error('attachment') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-2">
                <button class="btn" wire:click="$set('showScheduleModal', false)">Cancelar</button>
                <button class="btn btn-primary" wire:click="save">Salvar</button>
            </div>
        </div>
    </x-modal>
</div>
