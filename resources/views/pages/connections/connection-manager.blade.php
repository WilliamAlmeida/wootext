<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Conexões WhatsApp</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Gerencie suas instâncias do Evolution e WAHA.</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="btn btn-primary">
            <x-phosphor-plus class="size-4" />
            Nova Conexão
        </button>
    </div>

    <livewire:components.grid-instances provider="waha" lazy />
    <!-- <livewire:components.grid-instances provider="evolution" lazy /> -->

    {{-- Create Modal --}}
    @if($showCreateModal)
    <x-modal model="showCreateModal" title="Nova Conexão" maxWidth="md">
        <div class="space-y-4" x-data="{
            newInstanceProvider: $wire.entangle('newInstanceProvider'),
        }">
            <div class="space-y-4">
                <div>
                    <label class="label">Nome da Conexão</label>
                    <input type="text" wire:model="newInstanceName" placeholder="ex: Loja Principal" class="input" autofocus />
                    <p class="mt-1 text-xs text-zinc-500">Use um nome amigável para identificar esta instância.</p>
                    @error('newInstanceName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="label">Provedor</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer transition"
                            x-bind:class="newInstanceProvider === 'waha' ? 'border-sky-500 bg-sky-50 dark:bg-sky-900/20' : 'border-zinc-200 dark:border-zinc-800'">
                            <input type="radio" wire:model="newInstanceProvider" value="waha" class="sr-only" />
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">WAHA</span>
                            <span class="text-xs text-zinc-500">Recomendado</span>
                            <x-phosphor-check-circle-fill class="absolute top-2 right-2 size-4 text-sky-500" wire:show="newInstanceProvider === 'waha'" />
                        </label>
                        <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer transition"
                            x-bind:class="newInstanceProvider === 'evolution' ? 'border-sky-500 bg-sky-50 dark:bg-sky-900/20' : 'border-zinc-200 dark:border-zinc-800'">
                            <input type="radio" wire:model="newInstanceProvider" value="evolution" class="sr-only" />
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">Evolution API</span>
                            <span class="text-xs text-zinc-500">Alternativo</span>
                            <x-phosphor-check-circle-fill class="absolute top-2 right-2 size-4 text-sky-500" wire:show="newInstanceProvider === 'evolution'" />
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <button type="button" wire:click="$set('showCreateModal', false)" class="btn">Cancelar</button>
                <button type="button" wire:click="createInstance" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="createInstance">Criar Conexão</span>
                    <span wire:loading wire:target="createInstance">Criando...</span>
                </button>
            </div>
        </div>
    </x-modal>
    @endif

    {{-- Settings Modal --}}
    @if($showSettingsModal)
    <x-modal model="showSettingsModal" title="Configurações de Webhook" maxWidth="lg">
        <div class="space-y-4">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Use estas informações para configurar o webhook manualmente no seu provedor se necessário.
            </p>

            <div>
                <label class="label">URL do Webhook</label>
                <div class="flex gap-2">
                    <input type="text" readonly value="{{ $selectedWebhookUrl }}" class="input flex-1 bg-zinc-50 dark:bg-zinc-800" />
                    <button type="button" x-on:click="navigator.clipboard.writeText('{{ $selectedWebhookUrl }}'); $dispatch('notify', { type: 'success', message: 'Copiado!' })" class="btn btn-outline btn-xs">
                        Copiar
                    </button>
                </div>
            </div>

            <div>
                <label class="label">Token de Segurança (API Key)</label>
                <div class="flex gap-2">
                    <input type="text" readonly value="{{ $selectedToken }}" class="input flex-1 bg-zinc-50 dark:bg-zinc-800" />
                    <button type="button" x-on:click="navigator.clipboard.writeText('{{ $selectedToken }}'); $dispatch('notify', { type: 'success', message: 'Copiado!' })" class="btn btn-outline btn-xs">
                        Copiar
                    </button>
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-100 dark:border-blue-800">
                <div class="flex gap-2">
                    <x-phosphor-info class="size-5 text-blue-600 dark:text-blue-400 shrink-0" />
                    <p class="text-xs text-blue-700 dark:text-blue-300">
                        O sistema tenta configurar o webhook automaticamente ao criar a instância. Use estes dados apenas para integração manual ou externa.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-zinc-100 dark:border-zinc-800">
            <button type="button" wire:click="closeSettingsModal" class="btn btn-primary">Fechar</button>
        </div>
    </x-modal>
    @endif

    {{-- QR Code Modal --}}
    @if($showQrModal)
    <x-modal model="showQrModal" title="Conectar — {{ $qrInstanceName }}" maxWidth="sm">
        <div class="space-y-4">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Escaneie o QR Code com o WhatsApp (Aparelhos Conectados).</p>

            <div class="flex justify-center p-4 bg-white rounded-xl">
                @if ($qrCode)
                    <img src="{{ $qrCode }}" alt="QR Code" class="w-64 h-64" />
                @else
                    <div class="w-64 h-64 bg-zinc-100 dark:bg-zinc-800 rounded-lg flex items-center justify-center animate-pulse">
                        <x-phosphor-circle-notch class="size-8 text-zinc-400 animate-spin" />
                    </div>
                @endif
            </div>

            <div class="flex flex-col gap-2">
                <button type="button" wire:click="closeQrModal" class="btn btn-primary w-full">
                    Já escaneei
                </button>
                <button type="button" wire:click="closeQrModal" class="btn btn-ghost w-full">
                    Fechar
                </button>
            </div>
        </div>
    </x-modal>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-data 
         x-on:keydown.escape.window="$wire.cancelDelete">
        
        <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 rounded-xl shadow-xl overflow-hidden animate-in fade-in zoom-in duration-200"
             @click.outside="$wire.cancelDelete">
             
            <div class="p-6">
                {{-- Header --}}
                <div class="flex items-start gap-4 mb-6">
                    <div class="flex items-center justify-center p-3 bg-red-100 dark:bg-red-900/30 rounded-full shrink-0">
                        <x-phosphor-warning class="size-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                            Excluir Conexão
                        </h3>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            Tem certeza que deseja excluir a conexão 
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                "{{ $deleteTarget['friendly_name'] ?? $deleteTarget['name'] ?? '' }}"
                            </span>?
                            Esta ação não pode ser desfeita.
                        </p>
                    </div>
                </div>

                {{-- Checkbox --}}
                <div class="mb-6">
                    <label class="flex items-start gap-3 p-4 border border-zinc-200 dark:border-zinc-800 rounded-lg cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <input type="checkbox" wire:model="deleteInboxChecked" class="mt-1 w-4 h-4 text-red-600 border-zinc-300 rounded focus:ring-red-500 dark:bg-zinc-800 dark:border-zinc-700 focus:ring-offset-0" />
                        <div class="text-sm">
                            <span class="block font-medium text-zinc-900 dark:text-zinc-100">Excluir também caixa de entrada</span>
                            <span class="block text-zinc-500 dark:text-zinc-400 mt-0.5">Remove a inbox associada no Chatwoot e todo seu histórico.</span>
                        </div>
                    </label>
                </div>

                {{-- Footer --}}
                <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" 
                        wire:click="cancelDelete" 
                        class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="button" 
                        wire:click="confirmDelete" 
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="confirmDelete">
                        <span wire:loading.remove wire:target="confirmDelete">Sim, excluir</span>
                        <span wire:loading wire:target="confirmDelete" class="flex items-center gap-2">
                            <x-phosphor-circle-notch class="size-4 animate-spin" />
                            Excluindo...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
