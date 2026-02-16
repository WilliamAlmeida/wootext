<div>
    @if(auth()->user()->account_id)
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-md">
            Sua conta está atualmente vinculada a uma conta do Chatwoot. Você pode gerenciar a integração ou remover a vinculação abaixo.

            <div class="text-xs mt-2">
                <span class="font-semibold">Account ID:</span> {{ auth()->user()->account_id }}
            </div>
        </div>

        <div class="mt-10 border-t border-zinc-200 dark:border-zinc-700 pt-6">
            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 text-red-600 dark:text-red-400">Zona de Perigo</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                Ao removere a vinculação da sua conta, você não poderá mais acessar o Chatwoot através do Wootext. Tenha certeza do que está fazendo antes de prosseguir.
            </p>
            
            <button wire:click="logoutChatwoot" wire:confirm="Tem certeza que deseja remover a vinculação da sua conta com o Chatwoot?"
                wire:loading.attr="disabled" wire:loading.class="opacity-50" wire:target="logoutChatwoot"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                    Remover Vinculação
                    <x-phosphor-arrows-clockwise class="animate-spin ms-2" wire:loading wire:target="logoutChatwoot" />
            </button>

            <x-action-message class="ms-3" on="chatwoot-logged-out">
                Vinculação removida.
            </x-action-message>
        </div>
    @else
        <form wire:submit="signInChatwoot" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Email') }}</label>
                <input wire:model="email" id="email" type="email" required autocomplete="email" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                @error('email') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Password') }}</label>
                <input wire:model="password" id="password" type="password" required autocomplete="current-password" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                @error('password') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-50" wire:target="signInChatwoot"
                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        Autenticar
                        <x-phosphor-arrows-clockwise class="animate-spin ms-2" wire:loading wire:target="signInChatwoot" />
                    </button>
                </div>

                <x-action-message class="me-3" on="chatwoot-authenticated">
                    Autenticação bem-sucedida.
                </x-action-message>
            </div>
        </form>
    @endif
</div>
