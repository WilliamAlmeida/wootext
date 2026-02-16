<div>
    <div class="flex flex-col w-full mx-auto space-y-6 text-sm" wire:cloak>
        @if ($twoFactorEnabled)
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        {{ __('Enabled') }}
                    </span>
                </div>

                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ __('With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                </p>

                <livewire:settings.two-factor.recovery-codes :$requiresConfirmation/>

                <div class="flex justify-start">
                        <button type="button"
                        wire:click="disable"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                        <x-phosphor-shield class="w-5 h-5" />
                        {{ __('Disable 2FA') }}
                    </button>
                </div>
            </div>
        @else
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        {{ __('Disabled') }}
                    </span>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                </p>

                <button type="button"
                    wire:click="enable"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <x-phosphor-shield-check class="w-5 h-5" />
                    {{ __('Enable 2FA') }}
                </button>
            </div>
        @endif
    </div>

    <div x-data="{ showModal: @entangle('showModal') }" 
        x-cloak 
        @close-modal.window="showModal = false">
        
        <div x-show="showModal" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
            @keydown.escape.window="showModal = false; $wire.call('closeModal')">
            
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                @click="showModal = false; $wire.call('closeModal')"
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
                    <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        
                        <div class="p-6 space-y-6">
                            <div class="flex flex-col items-center space-y-4">
                                <div class="p-0.5 w-auto rounded-full border border-stone-100 dark:border-stone-600 bg-white dark:bg-stone-800 shadow-sm">
                                    <div class="p-2.5 rounded-full border border-stone-200 dark:border-stone-600 overflow-hidden bg-stone-100 dark:bg-stone-200 relative">
                                        <div class="flex items-stretch absolute inset-0 w-full h-full divide-x [&>div]:flex-1 divide-stone-200 dark:divide-stone-300 justify-around opacity-50">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <div></div>
                                            @endfor
                                        </div>

                                        <div class="flex flex-col items-stretch absolute w-full h-full divide-y [&>div]:flex-1 inset-0 divide-stone-200 dark:divide-stone-300 justify-around opacity-50">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <div></div>
                                            @endfor
                                        </div>

                                        <x-phosphor-qr-code class="relative z-20 w-6 h-6 dark:text-gray-900" />
                                    </div>
                                </div>

                                <div class="space-y-2 text-center">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->modalConfig['title'] }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $this->modalConfig['description'] }}</p>
                                </div>
                            </div>

                            @if ($showVerificationStep)
                                <div class="space-y-6">
                                    <div class="flex flex-col items-center space-y-3">
                                        <x-input-otp
                                            :digits="6"
                                            name="code"
                                            wire:model="code"
                                            autocomplete="one-time-code"
                                        />
                                        @error('code')
                                            <p class="text-sm text-red-600 dark:text-red-400">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <button type="button"
                                            wire:click="resetVerification"
                                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                            {{ __('Back') }}
                                        </button>

                                        <button type="button"
                                            wire:click="confirmTwoFactor"
                                            x-bind:disabled="$wire.code.length < 6"
                                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                            {{ __('Confirm') }}
                                        </button>
                                    </div>
                                </div>
                            @else
                                @error('setupData')
                                    <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
                                        <div class="flex">
                                            <x-phosphor-x-circle class="h-5 w-5 text-red-400" />
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">{{ $message }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                @enderror

                                <div class="flex justify-center">
                                    <div class="relative w-64 overflow-hidden border rounded-lg border-stone-200 dark:border-stone-700 aspect-square">
                                        @empty($qrCodeSvg)
                                            <div class="absolute inset-0 flex items-center justify-center bg-white dark:bg-stone-700 animate-pulse">
                                                        <x-phosphor-spinner-gap class="animate-spin h-8 w-8 text-gray-400" />
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center h-full p-4">
                                                <div class="bg-white p-3 rounded">
                                                    {!! $qrCodeSvg !!}
                                                </div>
                                            </div>
                                        @endempty
                                    </div>
                                </div>

                                <div>
                                    <button type="button"
                                        wire:click="showVerificationIfNecessary"
                                        :disabled="@js($errors->has('setupData'))"
                                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        {{ $this->modalConfig['buttonText'] }}
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div class="relative flex items-center justify-center w-full">
                                        <div class="absolute inset-0 w-full h-px top-1/2 bg-stone-200 dark:bg-stone-600"></div>
                                        <span class="relative px-2 text-sm bg-white dark:bg-gray-800 text-stone-600 dark:text-stone-400">
                                            {{ __('or, enter the code manually') }}
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center space-x-2"
                                        x-data="{
                                            copied: false,
                                            async copy() {
                                                try {
                                                    await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                                    this.copied = true;
                                                    setTimeout(() => this.copied = false, 1500);
                                                } catch (e) {
                                                    console.warn('Could not copy to clipboard');
                                                }
                                            }
                                        }"
                                    >
                                        <div class="flex items-stretch w-full border rounded-xl dark:border-stone-700">
                                            @empty($manualSetupKey)
                                                <div class="flex items-center justify-center w-full p-3 bg-stone-100 dark:bg-stone-700">
                                                        <x-phosphor-spinner-gap class="animate-spin h-5 w-5 text-gray-400" />
                                                </div>
                                            @else
                                                <input
                                                    type="text"
                                                    readonly
                                                    value="{{ $manualSetupKey }}"
                                                    class="w-full p-3 bg-transparent outline-none text-stone-900 dark:text-stone-100"
                                                />

                                                <button type="button"
                                                    @click="copy()"
                                                    class="px-3 transition-colors border-l cursor-pointer border-stone-200 dark:border-stone-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <x-phosphor-clipboard x-show="!copied" class="w-5 h-5" />
                                                    <x-phosphor-check class="w-5 h-5 text-green-500" x-show="copied" />
                                                </button>
                                            @endempty
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
