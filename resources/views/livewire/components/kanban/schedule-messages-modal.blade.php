<div>
    @if($showScheduleModal)
        {{-- Schedule Modal --}}
        <x-modal model="showScheduleModal" title="{{ $editingMessageId ? 'Editar Mensagem Agendada' : 'Agendar Nova Mensagem' }}" maxWidth="md">
            <div class="space-y-4">
                <div class="space-y-1">
                    <h5 class="text-sm text-gray-500 dark:text-zinc-400">
                        {{ $card?->custom_name }}
                        {{ 'Conversa #' . $card?->conversation_id }}
                    </h5>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Mensagem</label>
                    <textarea wire:model="content" placeholder="Digite sua mensagem" maxlength="1000" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
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
                    <button class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600 transition-colors" wire:click="$set('showScheduleModal', false)">Cancelar</button>
                    <button class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors" wire:click="saveSchedule">Salvar</button>
                </div>
            </div>

            {{-- List Messages --}}
            @island('scheduled-messages-list', lazy: true, always: true)
                @placeholder
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Mensagens Agendadas</h3>
                        <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Carregando mensagens agendadas...</p>
                        </div>
                    </div>
                @endplaceholder

                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Mensagens Agendadas</h3>
                    <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                        @forelse($this->getScheduledMessages as $scheduled)
                            @php($scheduledAttachments = $this->normalizeAttachments($scheduled->attachments))
                            <div class="p-3 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-gray-50 dark:bg-zinc-700">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $scheduled->message }}</p>
                                        @if(! empty($scheduledAttachments[0]['filePath']))
                                            <a href="{{ Storage::url($scheduledAttachments[0]['filePath']) }}" target="_blank" class="text-sm text-blue-500 hover:underline mt-1 block">
                                                {{ $scheduledAttachments[0]['originalName'] ?? 'Ver Anexo' }}
                                            </a>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-zinc-400 flex gap-x-1 items-center select-none pointer-events-none">
                                        <x-phosphor-calendar class="w-2 h-2" />
                                        {{ $scheduled->scheduled_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                <div class="mt-2">
                                    @if($scheduled->status === 'pending')
                                        <div class="flex gap-2 items-center select-none">
                                            <button class="text-sm text-blue-500 hover:underline cursor-pointer" wire:click="editSchedule({{ $scheduled->id }})">Editar</button>
                                            <button class="text-sm text-red-500 hover:underline cursor-pointer" wire:click="deleteSchedule({{ $scheduled->id }})" wire:confirm="Tem certeza que deseja deletar esta mensagem agendada?">Deletar</button>
                                        </div>
                                    @else
                                        <div class="flex gap-2 items-center justify-between">
                                            <span class="text-xs text-gray-500 flex gap-x-1 items-center">
                                                <x-phosphor-clock class="w-2 h-2" />
                                                {{ $scheduled->updated_at->diffForHumans() }}
                                            </span>
                                            @switch($scheduled->status)
                                                @case('sent')
                                                    <span class="text-xs text-green-500 flex gap-x-1 items-center">
                                                        <x-phosphor-check class="w-2 h-2" />
                                                    </span>
                                                    @break
                                                @case('error')
                                                    <span class="text-xs text-red-500 flex gap-x-1 items-center">
                                                        <x-phosphor-x class="w-2 h-2" />
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="text-xs text-yellow-500 flex gap-x-1 items-center">
                                                        <x-phosphor-hourglass-fill class="w-2 h-2 animate-spin" />
                                                    </span>
                                            @endswitch
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Nenhuma mensagem agendada.</p>
                        @endforelse
                    </div>
                </div>
            @endisland
        </x-modal>
    @endif
</div>