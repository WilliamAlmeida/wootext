<div>
    <form wire:submit="updateProfileInformation" class="space-y-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Name') }}</label>
            <input wire:model="name" id="name" type="text" required autofocus autocomplete="name" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
            @error('name') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Email') }}</label>
            <input wire:model="email" id="email" type="email" required autocomplete="email" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
            @error('email') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Your email address is unverified.') }}

                        <button type="button" wire:click.prevent="resendVerificationNotification" 
                            class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 underline cursor-pointer">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center justify-end">
                <button type="submit" 
                    class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    {{ __('Save') }}
                </button>
            </div>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>

    <div class="mt-10 border-t border-zinc-200 dark:border-zinc-700 pt-6">
        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 text-red-600 dark:text-red-400">Zona de Perigo</h3>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 mb-4">Uma vez que sua conta for excluída, todos os seus recursos e dados serão permanentemente apagados.</p>
        <livewire:settings.delete-user-form />
    </div>
</div>
