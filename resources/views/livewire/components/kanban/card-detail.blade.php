<div>
    @if($card)
        <div class="fixed inset-y-0 right-0 w-full max-w-lg bg-white dark:bg-zinc-900 shadow-2xl border-l border-zinc-200 dark:border-zinc-700 z-50 flex flex-col">
            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $card?->custom_name ?? 'Conversa #' . $conversationId }}
                    </h2>
                    @if ($card?->phone_number)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">{{ $card->phone_number }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="$dispatch('open-schedule-modal', [{{ $conversationId }}])" class="inline-flex items-center px-2 py-1.5 text-sm font-medium text-zinc-700 bg-transparent hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800 rounded-md transition-colors">
                        <x-phosphor-clock class="w-4 h-4" />
                    </button>
                    <button type="button" wire:click="$dispatch('open-move-card-modal', [{{ $conversationId }}])" class="inline-flex items-center px-2 py-1.5 text-sm font-medium text-zinc-700 bg-transparent hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800 rounded-md transition-colors">
                        <x-phosphor-arrows-down-up class="w-4 h-4" />
                    </button>
                    <button type="button" wire:click="$parent.closeCardDetail" class="inline-flex items-center px-2 py-1.5 text-sm font-medium text-zinc-700 bg-transparent hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800 rounded-md transition-colors">
                        <x-phosphor-x class="w-4 h-4" />
                    </button>
                </div>
            </div>

            {{-- Content --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-6">
                {{-- Location --}}
                @if ($card?->stage)
                    <div>
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-1">Localização</p>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full" style="background-color: {{ $card->stage->funnel->color ?? '#6B7280' }}"></span>
                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $card->stage->funnel->name ?? '—' }}</span>
                            <x-phosphor-caret-right class="w-3 h-3 text-zinc-400" />
                            <span class="inline-block w-2 h-2 rounded-full" style="background-color: {{ $card->stage->color ?? '#6B7280' }}"></span>
                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $card->stage->name }}</span>
                        </div>
                    </div>
                @endif

                {{-- Chatwoot Info --}}
                @if ($conversation)
                    <div>
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">Chatwoot</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-zinc-500">Status</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ match($conversation['status'] ?? '') { 
                                        'open' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', 
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', 
                                        'resolved' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200', 
                                        default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200' 
                                    } }}">
                                    {{ ucfirst($conversation['status'] ?? '—') }}
                                </span>
                            </div>
                            @if (! empty($conversation['meta']['sender']['name']))
                                <div>
                                    <p class="text-xs text-zinc-500">Contato</p>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $conversation['meta']['sender']['name'] }}</p>
                                </div>
                            @endif
                            @if (! empty($conversation['meta']['assignee']['name']))
                                <div>
                                    <p class="text-xs text-zinc-500">Atribuído</p>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $conversation['meta']['assignee']['name'] }}</p>
                                </div>
                            @endif
                            @if (! empty($conversation['inbox_id']))
                                <div>
                                    <p class="text-xs text-zinc-500">Inbox</p>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">#{{ $conversation['inbox_id'] }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-xs text-zinc-500">Prioridade</p>
                                @if (! empty($conversation['priority']))
                                    <x-kanban.card.badge-priority :priority="$conversation['priority']" />
                                @else
                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">—</span>
                                @endif
                            </div>
                            @if (! empty($conversation['labels']))
                                <div>
                                    <p class="text-xs text-zinc-500">Labels</p>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                        <template x-for="label in {{ json_encode($conversation['labels']) }}" :key="label">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 mr-1" x-text="label"></span>
                                        </template>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Custom Fields --}}
                @if ($card?->customFieldValues && $card->customFieldValues->isNotEmpty())
                    <div>
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">Campos Personalizados</p>
                        <div class="space-y-2">
                            @foreach ($card->customFieldValues as $cfv)
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-500">{{ $cfv->customField->name ?? '—' }}</span>
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $cfv->value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Tasks --}}
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">Tarefas</p>

                    @php $tasks = $this->getTasks(); @endphp
                    @if ($tasks->isNotEmpty())
                        <div class="space-y-1.5 mb-3">
                            @foreach ($tasks as $task)
                                <div class="flex items-center gap-2 group">
                                    <input type="checkbox" @checked($task->completed) wire:click="toggleTask({{ $task->id }})"
                                        class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-zinc-800 focus:ring-2 dark:bg-zinc-700 dark:border-zinc-600 cursor-pointer"
                                    />
                                    <span class="text-sm flex-1 text-zinc-900 dark:text-zinc-100 group-hover:font-semibold {{ $task->completed ? 'line-through text-zinc-400' : '' }}">
                                        {{ $task->title }}
                                    </span>
                                    <button type="button" wire:confirm="Você tem certeza que deseja excluir esta tarefa?" wire:click="deleteTask({{ $task->id }})"
                                        class="text-red-500 hover:text-red-600 group-hover:opacity-100 opacity-0 transition-opacity cursor-pointer">
                                        <x-phosphor-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <input
                            type="text"
                            wire:model="newTaskTitle"
                            wire:keydown.enter="addTask"
                            placeholder="Nova tarefa..."
                            class="flex-1 px-3 py-1.5 text-sm border border-zinc-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-100"
                        />
                        <button type="button" wire:click="addTask" class="inline-flex items-center px-2 py-1.5 text-sm font-medium text-zinc-700 bg-transparent hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800 rounded-md transition-colors">
                            <x-phosphor-plus class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                {{-- Note --}}
                <div x-data="{ notes: $wire.entangle('notes') }">
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-2">Nota Interna</p>
                    <textarea
                        wire:model="noteContent"
                        wire:keydown.ctrl.enter="sendNote"
                        placeholder="Escreva uma nota..."
                        rows="3"
                        class="w-full px-3 py-2 text-sm border border-zinc-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-100"
                    ></textarea>
                    <button type="button" wire:click="sendNote" class="my-2 inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                        Enviar Nota
                    </button>

                    <div class="max-h-40 overflow-y-auto border border-zinc-300 rounded-md" wire:show="notes.length > 0">
                        <template x-for="note in notes" :key="note.id">
                            <div class="flex flex-col gap-2 group hover:bg-gray-100 dark:hover:bg-gray-800 p-2 even:bg-gray-50 dark:even:bg-gray-800">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs" x-text="note.created_at_formatted"></span>
                                    <button type="button" x-on:click="confirm('Deseja realmente deletar esta nota?') && $wire.deleteNote(note.id)"
                                        class="text-red-500 hover:text-red-600 group-hover:opacity-100 opacity-0 transition-opacity cursor-pointer">
                                        <x-phosphor-trash class="w-4 h-4" />
                                    </button>
                                </div>
                                <div x-text="note.content"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <livewire:components.kanban.move-card />
        </div>
    @endif
</div>