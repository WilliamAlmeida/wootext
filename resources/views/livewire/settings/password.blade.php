<div>
    <form method="POST" wire:submit="updatePassword" class="space-y-6">
        <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Current password') }}</label>
            <input wire:model="current_password" id="current_password" type="password" required autocomplete="current-password"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
            @error('current_password') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('New password') }}</label>
            <input wire:model="password" id="password" type="password" required autocomplete="new-password"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
            @error('password') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Confirm Password') }}</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" required autocomplete="new-password"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
            @error('password_confirmation') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center justify-end">
                <button type="submit" 
                    class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    {{ __('Save') }}
                </button>
            </div>

            <x-action-message class="me-3" on="password-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</div>
