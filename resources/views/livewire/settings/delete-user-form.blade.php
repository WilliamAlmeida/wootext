<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Delete account') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('Delete your account and all of its resources') }}</p>
    </div>

    <div x-data="{ showModal: @js($errors->isNotEmpty()) }">
        <button type="button" 
            @click="showModal = true"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
            {{ __('Delete account') }}
        </button>

        <div x-show="showModal" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto" 
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
            @keydown.escape.window="showModal = false">
            
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                @click="showModal = false"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
            </div>

            <!-- Modal panel -->
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        
                        <form method="POST" wire:submit="deleteUser" class="p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ __('Are you sure you want to delete your account?') }}
                                </h3>

                                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                                </p>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Password') }}</label>
                                <input wire:model="password" id="password" type="password"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                @error('password') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                                <button type="button" 
                                    @click="showModal = false"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                                    {{ __('Cancel') }}
                                </button>

                                <button type="submit"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                    {{ __('Delete account') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
