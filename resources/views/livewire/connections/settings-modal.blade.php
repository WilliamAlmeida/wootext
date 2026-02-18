<div>
    @if($showSettingsModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg max-h-[90vh] bg-white dark:bg-zinc-900 rounded-xl shadow-xl overflow-hidden flex flex-col">

                {{-- Header --}}
                <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-800 shrink-0">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Detalhes da Conexão</h3>
                    <button type="button" wire:click="close" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                        <x-phosphor-x class="size-6" />
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-6 overflow-y-auto flex-1 space-y-6">

                    {{-- Instance Info --}}
                    <div class="flex gap-6 items-start py-4">
                        <div class="shrink-0">
                            <div class="size-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                <svg class="size-8 text-green-600 dark:text-green-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="mb-4">
                                <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-2">{{ $friendlyName ?? $instanceName }}</h4>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full border
                                    {{ $statusLabel === 'Conectado'
                                        ? 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800'
                                        : 'bg-yellow-100 text-yellow-700 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-800' }}">
                                    <span class="size-2 rounded-full bg-current"></span>
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                                <div>
                                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Nome da Instância</span>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100 mt-1 font-medium">{{ $friendlyName ?? $instanceName }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Provedor</span>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100 mt-1 font-medium">{{ ucfirst($provider) }}</p>
                                </div>
                                @if($provider === 'waha' && !empty($sessionDetails['me']['pushName']))
                                    <div>
                                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Nome do Perfil</span>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100 mt-1 font-medium">{{ $sessionDetails['me']['pushName'] }}</p>
                                    </div>
                                @endif
                                @if($provider === 'evolution')
                                    <div>
                                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Integração</span>
                                        <p class="text-sm text-zinc-900 dark:text-zinc-100 mt-1">WHATSAPP-BAILEYS</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Settings --}}
                    <div class="border-t border-zinc-200 dark:border-zinc-800 pt-4 space-y-4">

                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                @if($provider === 'evolution')
                                    Configurações da Instância
                                @else
                                    Configurações do Webhook
                                @endif
                            </h4>
                            <div class="flex items-center gap-2 text-xs">
                                @if($loading)
                                    <span class="text-zinc-500 flex items-center gap-1">
                                        <svg class="animate-spin size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Carregando...
                                    </span>
                                @endif
                                @if($saving)
                                    <span class="text-zinc-500">Salvando...</span>
                                @endif
                            </div>
                        </div>

                        @if($errorMessage)
                            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-900/50 rounded-lg p-3 flex items-start gap-2">
                                <x-phosphor-warning-circle class="size-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                                <p class="text-sm text-red-800 dark:text-red-300 flex-1">{{ $errorMessage }}</p>
                                <button wire:click="$set('errorMessage', null)" class="text-red-400 hover:text-red-600">
                                    <x-phosphor-x class="size-4" />
                                </button>
                            </div>
                        @endif

                        @if(!$loading)
                            @if($provider === 'evolution')
                                <div class="space-y-0">
                                    {{-- Mostrar Nome do Agente --}}
                                    <div class="flex items-center justify-between py-3">
                                        <div class="flex-1 pr-4">
                                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Mostrar Nome do Agente</span>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Exibe o nome do agente do Chatwoot nas conversas com clientes</p>
                                        </div>
                                        <button type="button" wire:click="toggleSetting('signMsg')"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                                {{ $settings['signMsg'] ? 'bg-blue-600' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                                            <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                                {{ $settings['signMsg'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </div>

                                    {{-- Ignorar Grupos --}}
                                    <div class="flex items-center justify-between py-3 border-t border-zinc-100 dark:border-zinc-800">
                                        <div class="flex-1 pr-4">
                                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Ignorar Grupos</span>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Não receber mensagens de grupos do WhatsApp</p>
                                        </div>
                                        <button type="button" wire:click="toggleSetting('groupsIgnore')"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                                {{ $settings['groupsIgnore'] ? 'bg-blue-600' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                                            <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                                {{ $settings['groupsIgnore'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </div>

                                    {{-- Rejeitar Ligações --}}
                                    <div class="border-t border-zinc-100 dark:border-zinc-800">
                                        <div class="flex items-center justify-between py-3">
                                            <div class="flex-1 pr-4">
                                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Rejeitar Ligações</span>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Rejeita automaticamente chamadas de voz e vídeo</p>
                                            </div>
                                            <button type="button" wire:click="toggleSetting('rejectCall')"
                                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                                    {{ $settings['rejectCall'] ? 'bg-blue-600' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                                                <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                                    {{ $settings['rejectCall'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                            </button>
                                        </div>
                                        @if($settings['rejectCall'])
                                            <div class="pb-3">
                                                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Mensagem de Rejeição</label>
                                                <div class="flex gap-2">
                                                    <input type="text"
                                                        wire:model.defer="settings.msgCall"
                                                        placeholder="Digite a mensagem a ser enviada..."
                                                        class="input flex-1 text-sm" />
                                                    <button type="button" wire:click="saveCallMessage" wire:loading.attr="disabled"
                                                        class="btn btn-primary btn-sm shrink-0">
                                                        <span wire:loading.remove wire:target="saveCallMessage">Salvar</span>
                                                        <span wire:loading wire:target="saveCallMessage">...</span>
                                                    </button>
                                                </div>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Esta mensagem será enviada automaticamente quando uma ligação for rejeitada</p>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Sempre Online --}}
                                    <div class="flex items-center justify-between py-3 border-t border-zinc-100 dark:border-zinc-800">
                                        <div class="flex-1 pr-4">
                                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Sempre Online</span>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Mantém o status sempre como "online"</p>
                                        </div>
                                        <button type="button" wire:click="toggleSetting('alwaysOnline')"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                                {{ $settings['alwaysOnline'] ? 'bg-blue-600' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                                            <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                                {{ $settings['alwaysOnline'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </div>

                                    {{-- Confirmar Leitura --}}
                                    <div class="flex items-center justify-between py-3 border-t border-zinc-100 dark:border-zinc-800">
                                        <div class="flex-1 pr-4">
                                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Confirmar Leitura de Mensagens</span>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Marca mensagens como lidas automaticamente</p>
                                        </div>
                                        <button type="button" wire:click="toggleSetting('readMessages')"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                                {{ $settings['readMessages'] ? 'bg-blue-600' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                                            <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                                {{ $settings['readMessages'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </div>

                                    {{-- Confirmar Visualização de Status --}}
                                    <div class="flex items-center justify-between py-3 border-t border-zinc-100 dark:border-zinc-800">
                                        <div class="flex-1 pr-4">
                                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Confirmar Visualização de Status</span>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Marca status (stories) como visualizados automaticamente</p>
                                        </div>
                                        <button type="button" wire:click="toggleSetting('readStatus')"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                                {{ $settings['readStatus'] ? 'bg-blue-600' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                                            <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                                {{ $settings['readStatus'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </div>
                                </div>
                            @else
                                {{-- WAHA: toggles + webhook --}}
                                <div class="space-y-0">
                                    {{-- Ignorar Grupos --}}
                                    <div class="flex items-center justify-between py-3">
                                        <div class="flex-1 pr-4">
                                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Ignorar Grupos</span>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Não receber mensagens de grupos do WhatsApp</p>
                                        </div>
                                        <button type="button" wire:click="toggleSetting('groupsIgnore')"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                                {{ $settings['groupsIgnore'] ? 'bg-blue-600' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                                            <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                                {{ $settings['groupsIgnore'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </div>

                                    {{-- Sempre Online --}}
                                    <div class="flex items-center justify-between py-3 border-t border-zinc-100 dark:border-zinc-800">
                                        <div class="flex-1 pr-4">
                                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Sempre Online</span>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Mantém o status sempre como "online" ao conectar</p>
                                        </div>
                                        <button type="button" wire:click="toggleSetting('alwaysOnline')"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                                {{ $settings['alwaysOnline'] ? 'bg-blue-600' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                                            <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                                {{ $settings['alwaysOnline'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </div>

                                    {{-- Confirmar Visualização de Status --}}
                                    <div class="flex items-center justify-between py-3 border-t border-zinc-100 dark:border-zinc-800">
                                        <div class="flex-1 pr-4">
                                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Confirmar Visualização de Status</span>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Marca status (stories) como visualizados automaticamente</p>
                                        </div>
                                        <button type="button" wire:click="toggleSetting('ignoreStatus')"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                                {{ !$settings['ignoreStatus'] ? 'bg-blue-600' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                                            <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                                {{ !$settings['ignoreStatus'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Webhook section --}}
                                <div class="border-t border-zinc-200 dark:border-zinc-800 pt-4 space-y-3">
                                    <h5 class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Webhook</h5>

                                    <div>
                                        <label class="label">URL do Webhook</label>
                                        <div class="flex gap-2">
                                            <input type="text" readonly value="{{ $webhookUrl }}" class="input flex-1 bg-zinc-50 dark:bg-zinc-800" />
                                            <button type="button"
                                                x-on:click="navigator.clipboard.writeText('{{ $webhookUrl }}'); $dispatch('notify', { type: 'success', message: 'Copiado!' })"
                                                class="btn btn-outline btn-xs">
                                                <x-phosphor-clipboard class="size-4" />
                                                Copiar
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end">
                                        <button type="button" wire:click="applyWahaWebhook" wire:loading.attr="disabled" class="btn btn-primary">
                                            <span wire:loading.remove wire:target="applyWahaWebhook">Aplicar webhook padrão</span>
                                            <span wire:loading wire:target="applyWahaWebhook">Aplicando...</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Disconnect button (Evolution only) --}}
                    @if($provider === 'evolution')
                        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-4">
                            <button type="button"
                                wire:click="disconnectInstance"
                                wire:confirm="Tem certeza que deseja desconectar esta instância? Você precisará escanear o QR Code novamente para reconectar."
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                <x-phosphor-power class="size-5" />
                                Desconectar Instância
                            </button>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 text-center mt-2">
                                Você precisará escanear o QR Code novamente para reconectar
                            </p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @endif
</div>
