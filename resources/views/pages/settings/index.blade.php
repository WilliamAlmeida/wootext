<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
        <aside class="py-6 px-2 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3">
            <nav class="space-y-1">
                <button wire:click="setTab('profile')"
                    class="{{ $activeTab === 'profile' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }} group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full transition-colors">
                    <x-phosphor-user class="{{ $activeTab === 'profile' ? 'text-blue-500' : 'text-zinc-400 group-hover:text-zinc-500' }} flex-shrink-0 -ml-1 mr-3 h-6 w-6" />
                    <span class="truncate">Perfil</span>
                </button>

                <button wire:click="setTab('password')"
                    class="{{ $activeTab === 'password' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }} group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full transition-colors">
                    <x-phosphor-lock-key class="{{ $activeTab === 'password' ? 'text-blue-500' : 'text-zinc-400 group-hover:text-zinc-500' }} flex-shrink-0 -ml-1 mr-3 h-6 w-6" />
                    <span class="truncate">Senha</span>
                </button>

                <button wire:click="setTab('chatwoot')"
                    class="{{ $activeTab === 'chatwoot' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }} group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full transition-colors">
                    <x-phosphor-chat-teardrop-dots class="{{ $activeTab === 'chatwoot' ? 'text-blue-500' : 'text-zinc-400 group-hover:text-zinc-500' }} flex-shrink-0 -ml-1 mr-3 h-6 w-6" />
                    <span class="truncate">Chatwoot</span>
                </button>

                <!-- <button wire:click="setTab('two-factor')"
                    class="{{ $activeTab === 'two-factor' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }} group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full transition-colors">
                    <x-phosphor-shield-check class="{{ $activeTab === 'two-factor' ? 'text-blue-500' : 'text-zinc-400 group-hover:text-zinc-500' }} flex-shrink-0 -ml-1 mr-3 h-6 w-6" />
                    <span class="truncate">Autenticação 2FA</span>
                </button> -->

                <!-- <button wire:click="setTab('appearance')"
                    class="{{ $activeTab === 'appearance' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }} group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full transition-colors">
                    <x-phosphor-paint-brush class="{{ $activeTab === 'appearance' ? 'text-blue-500' : 'text-zinc-400 group-hover:text-zinc-500' }} flex-shrink-0 -ml-1 mr-3 h-6 w-6" />
                    <span class="truncate">Aparência</span>
                </button> -->
            </nav>
        </aside>

        <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
            @if ($activeTab === 'profile')
                <div class="shadow sm:rounded-md sm:overflow-hidden bg-white dark:bg-zinc-900 py-6 px-4 sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-zinc-100">Perfil</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Atualize suas informações de perfil e endereço de e-mail.</p>
                    </div>
                    <div class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-6">
                        <livewire:settings.profile />
                    </div>
                </div>
            @endif

            @if ($activeTab === 'password')
                <div class="shadow sm:rounded-md sm:overflow-hidden bg-white dark:bg-zinc-900 py-6 px-4 sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-zinc-100">Senha</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Certifique-se de que sua conta esteja usando uma senha longa e aleatória para ficar segura.</p>
                    </div>
                    <div class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-6">
                        <livewire:settings.password lazy />
                    </div>
                </div>
            @endif

            @if ($activeTab === 'two-factor')
                <div class="shadow sm:rounded-md sm:overflow-hidden bg-white dark:bg-zinc-900 py-6 px-4 sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-zinc-100">Autenticação de Dois Fatores</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Adicione segurança adicional à sua conta usando autenticação de dois fatores.</p>
                    </div>
                    <div class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-6">
                         <livewire:settings.two-factor lazy />
                    </div>
                </div>
            @endif

            @if ($activeTab === 'appearance')
                <div class="shadow sm:rounded-md sm:overflow-hidden bg-white dark:bg-zinc-900 py-6 px-4 sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-zinc-100">Aparência</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Atualize as configurações de aparência da sua conta.</p>
                    </div>
                    <div class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-6">
                         <livewire:settings.appearance lazy />
                    </div>
                </div>
            @endif

            @if ($activeTab === 'chatwoot')
                <div class="shadow sm:rounded-md sm:overflow-hidden bg-white dark:bg-zinc-900 py-6 px-4 sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-zinc-100">Chatwoot</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Vincule sua conta do Chatwoot para integrar com o Wootext.</p>
                    </div>
                    <div class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-6">
                        <livewire:settings.chatwoot />
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
