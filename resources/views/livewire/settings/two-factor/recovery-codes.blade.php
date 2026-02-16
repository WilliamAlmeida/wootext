<div
    class="py-6 space-y-6 border shadow-sm rounded-xl border-zinc-200 dark:border-white/10"
    wire:cloak
    x-data="{ showRecoveryCodes: false }"
>
    <div class="px-6 space-y-2">
        <div class="flex items-center gap-2">
            <x-phosphor-lock class="w-4 h-4" />
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('2FA Recovery Codes') }}</h3>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}
        </p>
    </div>

    <div class="px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <button type="button"
                x-show="!showRecoveryCodes"
                @click="showRecoveryCodes = true;"
                aria-expanded="false"
                aria-controls="recovery-codes-section"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <x-phosphor-eye class="w-5 h-5" />
                {{ __('View Recovery Codes') }}
            </button>

            <button type="button"
                x-show="showRecoveryCodes"
                @click="showRecoveryCodes = false"
                aria-expanded="true"
                aria-controls="recovery-codes-section"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <x-phosphor-eye-slash class="w-5 h-5" />
                {{ __('Hide Recovery Codes') }}
            </button>

            @if (filled($recoveryCodes))
                <button type="button"
                    x-show="showRecoveryCodes"
                    wire:click="regenerateRecoveryCodes"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <x-phosphor-arrows-clockwise class="w-5 h-5" />
                    {{ __('Regenerate Codes') }}
                </button>
            @endif
        </div>

        <div
            x-show="showRecoveryCodes"
            x-transition
            id="recovery-codes-section"
            class="relative overflow-hidden"
            x-bind:aria-hidden="!showRecoveryCodes"
        >
            <div class="mt-3 space-y-3">
                @error('recoveryCodes')
                    <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
                        <div class="flex">
                            <x-phosphor-x-circle class="h-5 w-5 text-red-400" />
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">{{$message}}</h3>
                            </div>
                        </div>
                    </div>
                @enderror

                @if (filled($recoveryCodes))
                    <div
                        class="grid gap-1 p-4 font-mono text-sm rounded-lg bg-zinc-100 dark:bg-white/5"
                        role="list"
                        aria-label="Recovery codes"
                    >
                        @foreach($recoveryCodes as $code)
                            <div
                                role="listitem"
                                class="select-text"
                                wire:loading.class="opacity-50 animate-pulse"
                            >
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        {{ __('Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate Codes above.') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
