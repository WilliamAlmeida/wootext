<div>
    @if($showMoveModal)
        {{-- Move Modal --}}
        <div class="fixed inset-0 z-40 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-all backdrop-blur-xs bg-opacity-75 dark:bg-zinc-900 dark:bg-opacity-75" wire:click="$set('showMoveModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="relative z-10 inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-zinc-800 shadow-xl rounded-2xl" role="dialog" aria-modal="true">
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Mover Card</h2>
                            <h5 class="text-sm text-gray-500 dark:text-zinc-400">
                                {{ $card?->custom_name }}
                                {{ 'Conversa #' . $card?->conversation_id }}
                            </h5>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Funil</label>
                            <select
                                wire:model.live="moveFunnelId"
                                class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:border-zinc-600 dark:text-zinc-100"
                            >
                                <option value="">Selecione...</option>
                                @foreach ($this->availableFunnels as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Etapa</label>
                            <select
                                wire:model="moveStageId"
                                class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:border-zinc-600 dark:text-zinc-100"
                            >
                                <option value="">Selecione...</option>
                                @foreach ($this->availableStages as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="$set('showMoveModal', false)" class="inline-flex items-center px-3 py-2 text-sm font-medium text-zinc-700 bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600 rounded-md transition-colors">
                                Cancelar
                            </button>
                            <button type="button" wire:click="moveCard" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                                Mover
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif
</div>