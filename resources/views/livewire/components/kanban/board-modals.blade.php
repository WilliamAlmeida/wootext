<div>
    @if($showFunnelModal)
        {{-- Funnel Modal --}}
        <x-modal model="showFunnelModal" title="{{ $editingFunnelId ? 'Editar Funil' : 'Novo Funil' }}" maxWidth="md">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nome</label>
                    <input type="text" wire:model="newFunnelName" placeholder="Ex: Vendas" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    @error('newFunnelName') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Cor</label>
                    <input type="color" wire:model="newFunnelColor" class="h-10 w-20 rounded cursor-pointer" />
                    @error('newFunnelColor') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button class="btn" wire:click="$set('showFunnelModal', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="saveFunnel">Salvar</button>
                </div>
            </div>
        </x-modal>
    @endif

    @if($showStageModal)
        {{-- Stage Modal --}}
        <x-modal model="showStageModal" title="{{ $editingStageId ? 'Editar Etapa' : 'Nova Etapa' }}" maxWidth="md">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nome</label>
                    <input type="text" wire:model="newStageName" placeholder="Ex: Em Progresso" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    @error('newStageName') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Cor</label>
                    <input type="color" wire:model="newStageColor" class="h-10 w-20 rounded cursor-pointer" />
                    @error('newStageColor') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button class="btn" wire:click="$set('showStageModal', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="saveStage">Salvar</button>
                </div>
            </div>
        </x-modal>
    @endif

    <livewire:components.schedule-messages-modal />
</div>
