<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Dashboard</h1>
        
        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Funnels Widget -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-phosphor-funnel class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                                    Funnels Ativos
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ $this->funnelsCount }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                    <div class="text-sm">
                        <a href="{{ route('kanban') }}" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                            Ver todos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Cards Widget -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-phosphor-cards class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                                    Total de Cards
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ $this->cardsCount }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                    <div class="text-sm">
                        <a href="{{ route('kanban') }}" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                            Ir para Kanban
                        </a>
                    </div>
                </div>
            </div>

            <!-- Connections Widget -->
            @island(name: 'connections', lazy:true, defer: true)
                @placeholder
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <x-phosphor-plugs-connected class="h-6 w-6 text-gray-400" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                                            Conexões
                                        </dt>
                                        <dd>
                                            <div class="text-lg font-medium bg-gray-50 dark:bg-gray-600 animate-pulse h-7 w-10"></div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3 h-11">
                        </div>
                    </div>
                @endplaceholder

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-phosphor-plugs-connected class="h-6 w-6 text-gray-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                                        Conexões
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                            {{ $this->connectionsCount }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                        <div class="text-sm">
                            <a href="{{ route('connections') }}" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                                Gerenciar conexões
                            </a>
                        </div>
                    </div>
                </div>
            @endisland
        </div>
    </div>
</div>
