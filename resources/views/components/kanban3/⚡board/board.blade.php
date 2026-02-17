<div x-data="kanbanBoard($wire)" x-init="init()">
    <div class="flex flex-col h-full">
        <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-x-auto max-w-screen">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Kanban</h1>

                <div class="flex items-center gap-1 ml-4">
                    <template x-for="funnel in funnels" :key="funnel.id">
                        <button
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg transition-colors cursor-pointer text-nowrap"
                            x-bind:class="activeFunnelId === funnel.id
                                ? 'bg-blue-600 text-white hover:bg-blue-700'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700'"
                            @click="selectFunnel(funnel.id)"
                        >
                            <span class="inline-block w-2 h-2 rounded-full mr-1.5" :style="`background-color: ${funnel.color}`"></span>
                            <span x-text="funnel.name"></span>
                            <template x-if="funnel.is_system">
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">Sistema</span>
                            </template>
                        </button>
                    </template>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-phosphor-magnifying-glass class="h-4 w-4 text-gray-400" />
                    </div>
                    <input
                        x-model="search"
                        @input="debouncedSearch()"
                        type="text"
                        class="block w-full py-1 pl-10 sm:text-sm border-gray-300 dark:border-zinc-700 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-zinc-100 placeholder-zinc-400"
                        placeholder="Buscar cards..."
                    >
                </div>

                <button
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors"
                    @click="$wire.dispatch('open-stage-modal', { funnelId: activeFunnelId })"
                >
                    <x-phosphor-plus class="w-4 h-4 mr-1" />
                    Etapa
                </button>

                <button
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors"
                    @click="$wire.dispatch('open-funnel-modal')"
                >
                    <x-phosphor-plus class="w-4 h-4 mr-1" />
                    Funil
                </button>

                <button
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors"
                    @click="refreshBoard()"
                >
                    <x-phosphor-arrows-clockwise class="w-4 h-4 mr-1" x-bind:class="loading ? 'animate-spin' : ''" />
                    Atualizar
                </button>

                <template x-if="activeFunnel && !activeFunnel.is_system">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="inline-flex items-center justify-center px-2 py-1.5 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors">
                            <x-phosphor-dots-three-vertical class="w-4 h-4" />
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-zinc-800 ring-1 ring-black ring-opacity-5 z-50">
                            <ul class="py-1">
                                <li>
                                    <button @click="$wire.dispatch('open-funnel-modal', { funnelId: activeFunnelId }); open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700">
                                        <x-phosphor-pencil class="w-4 h-4 mr-2" />
                                        Editar Funil
                                    </button>
                                </li>
                                <li>
                                    <button @click="open = false; deleteFunnel()" class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <x-phosphor-trash class="w-4 h-4 mr-2" />
                                        Excluir Funil
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex-1 overflow-x-auto overflow-y-hidden py-4">
            <template x-if="loading">
                <div class="flex gap-4 h-full max-w-screen sm:max-w-[calc(100vw-20rem)] px-4">
                    <template x-for="i in 4" :key="i">
                        <div class="flex flex-col w-80 min-w-[320px] bg-zinc-100 dark:bg-zinc-800 rounded-xl shrink-0 animate-pulse">
                            <div class="h-12 border-b border-zinc-200 dark:border-zinc-700"></div>
                            <div class="p-3 space-y-2">
                                <div class="h-24 rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>
                                <div class="h-24 rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!loading && stages.length">
                <div class="flex gap-4 h-full max-w-screen sm:max-w-[calc(100vw-20rem)] px-4">
                    <template x-for="stage in stages" :key="stage.id">
                        <div class="flex flex-col w-80 min-w-[320px] bg-zinc-100 dark:bg-zinc-800 rounded-xl shrink-0">
                            <div class="flex items-center justify-between px-3 py-2.5 border-b border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full" :style="`background-color: ${stage.color}`"></span>
                                    <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100" x-text="stage.name"></span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300" x-text="stage.total_cards"></span>
                                </div>

                                <div x-data="{ open: false }" class="relative" x-cloak>
                                    <button @click="open = !open" class="inline-flex items-center justify-center p-1 text-sm font-medium rounded-lg bg-transparent text-gray-700 hover:bg-gray-200 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors">
                                        <x-phosphor-dots-three-vertical class="w-4 h-4" />
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-zinc-800 ring-1 ring-gray-300 ring-opacity-5 z-50">
                                        <ul class="py-1">
                                            <li>
                                                <button @click="$wire.dispatch('open-stage-modal', { stageId: stage.id }); open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700">
                                                    <x-phosphor-pencil class="w-4 h-4 mr-2" />
                                                    Editar
                                                </button>
                                            </li>
                                            <li>
                                                <button @click="open = false; deleteStage(stage.id)" class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                    <x-phosphor-trash class="w-4 h-4 mr-2" />
                                                    Excluir
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-1 overflow-y-auto p-2 space-y-2 max-h-[calc(100vh-240px)] sm:max-h-[73vh]" :data-stage-id="stage.id" x-ref="sortableColumns">
                                <template x-for="card in stage.cards" :key="card.id">
                                    <div
                                        class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm hover:shadow-md transition-shadow cursor-grab active:cursor-grabbing"
                                        :data-card-id="card.id"
                                    >
                                        <div class="p-3">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <img
                                                        :src="card.image_url || `https://ui-avatars.com/api/?name=${encodeURIComponent((card.contact_name || 'Contato').charAt(0))}&background=random&color=fff&size=64`"
                                                        :alt="card.contact_name || 'Contato'"
                                                        loading="lazy"
                                                        class="w-8 h-8 rounded-full object-cover mr-2"
                                                    >
                                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate" x-text="card.display_name || card.custom_name || `Conversa #${card.conversation_id}`"></p>
                                                    <template x-if="card.phone_number">
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5" x-text="card.phone_number"></p>
                                                    </template>
                                                </div>

                                                <template x-if="(card.unread_count || 0) > 0">
                                                    <span class="mt-0.5 text-white bg-red-500 rounded-lg w-5 h-5 text-xs flex items-center justify-center mr-1" x-text="card.unread_count"></span>
                                                </template>

                                                <div x-data="{ open: false }" class="relative" x-cloak>
                                                    <button @click="open = !open" class="inline-flex items-center justify-center p-0.5 text-sm font-medium rounded-lg bg-transparent text-gray-700 hover:bg-gray-200 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors">
                                                        <x-phosphor-dots-three-vertical class="w-4 h-4" />
                                                    </button>
                                                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-zinc-800 ring-1 ring-gray-300 ring-opacity-5 z-50">
                                                        <ul class="py-1">
                                                            <li>
                                                                <button @click="$wire.openCardDetail(card.conversation_id); open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700">
                                                                    <x-phosphor-eye class="w-4 h-4 mr-2" />
                                                                    Detalhes
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button @click="$wire.dispatch('open-schedule-modal', { conversationId: card.conversation_id }); open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700">
                                                                    <x-phosphor-clock class="w-4 h-4 mr-2" />
                                                                    Agendar Mensagem
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <a :href="conversationUrl(card)" target="_blank" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700">
                                                                    <x-phosphor-chat-circle-dots class="w-4 h-4 mr-2"/>
                                                                    Ver Conversa
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <hr class="my-1 border-t border-zinc-200 dark:border-zinc-700">
                                                            </li>
                                                            <li>
                                                                <button @click="open = false; deleteCard(card.id)" class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                                    <x-phosphor-trash class="w-4 h-4 mr-2" />
                                                                    Excluir
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300" x-text="`#${card.conversation_id}`"></span>
                                                <template x-if="card.priority">
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                        x-bind:class="priorityClass(card.priority)"
                                                        x-text="card.priority"
                                                    ></span>
                                                </template>
                                                <template x-if="card.assignee">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300" x-text="card.assignee"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div
                                    x-show="stage.has_more"
                                    x-intersect="loadMore(stage.id)"
                                    class="h-10 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700 flex items-center justify-center text-xs text-zinc-500 dark:text-zinc-400"
                                >
                                    Carregando mais...
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!loading && !stages.length">
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <x-phosphor-columns class="w-12 h-12 text-zinc-400 mx-auto mb-3" />
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Nenhum funil encontrado</h2>
                        <p class="mt-1 text-zinc-600 dark:text-zinc-400">Crie um funil para começar a organizar suas conversas.</p>
                        <button class="inline-flex items-center px-4 py-2 mt-4 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors" @click="$wire.dispatch('open-funnel-modal')">
                            Criar Funil
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="error" x-transition class="fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50" x-text="error"></div>
</div>

<script>
document.addEventListener('livewire:init', () => {
    Alpine.data('kanbanBoard', (wire) => ({
        loading: true,
        error: null,
        funnels: [],
        activeFunnel: null,
        activeFunnelId: null,
        stages: [],
        search: '',
        cardsPerStage: 10,
        searchTimeout: null,
        sortables: [],
        chatwootBaseUrl: @js(str(config('services.chatwoot.url'))->replace('https', 'http')->toString()),

        async init() {
            await this.fetchBoard();

            this.listeners = [
                Livewire.on('funnel-saved', async () => await this.fetchBoard()),
                Livewire.on('stage-saved', async () => await this.fetchBoard()),
                Livewire.on('card-moved', async () => await this.fetchBoard()),
            ];
        },

        destroy() {
            this.listeners?.forEach((stop) => stop());
            this.destroySortables();
        },

        async fetchBoard() {
            this.loading = true;
            this.error = null;

            try {
                const payload = await wire.getBoardData(this.cardsPerStage);
                this.applyBoard(payload);
            } catch (e) {
                this.error = 'Erro ao carregar o board.';
            } finally {
                this.loading = false;
                this.$nextTick(() => this.initSortables());
            }
        },

        applyBoard(payload) {
            this.funnels = payload.funnels ?? [];
            this.activeFunnel = payload.activeFunnel ?? null;
            this.activeFunnelId = payload.activeFunnelId ?? null;
            this.search = payload.search ?? '';
            this.stages = (payload.stages ?? []).map((stage) => ({
                ...stage,
                cards: stage.cards ?? [],
                loadingMore: false,
            }));
        },

        async selectFunnel(funnelId) {
            this.loading = true;

            try {
                const payload = await wire.selectFunnelJson(funnelId, this.cardsPerStage);
                this.applyBoard(payload);
            } catch (e) {
                this.error = 'Erro ao selecionar funil.';
            } finally {
                this.loading = false;
                this.$nextTick(() => this.initSortables());
            }
        },

        debouncedSearch() {
            clearTimeout(this.searchTimeout);

            this.searchTimeout = setTimeout(async () => {
                try {
                    const payload = await wire.updateSearch(this.search, this.cardsPerStage);
                    this.applyBoard(payload);
                    this.$nextTick(() => this.initSortables());
                } catch (e) {
                    this.error = 'Erro ao buscar cards.';
                }
            }, 300);
        },

        async refreshBoard() {
            this.loading = true;

            try {
                const payload = await wire.refreshBoard(this.cardsPerStage);
                this.applyBoard(payload);
            } catch (e) {
                this.error = 'Erro ao atualizar board.';
            } finally {
                this.loading = false;
                this.$nextTick(() => this.initSortables());
            }
        },

        async loadMore(stageId) {
            const stage = this.stages.find((item) => item.id === stageId);

            if (!stage || stage.loadingMore || !stage.has_more) {
                return;
            }

            stage.loadingMore = true;

            try {
                const response = await wire.loadMoreCards(stageId, stage.cards.length, this.cardsPerStage);
                stage.cards.push(...(response.cards ?? []));
                stage.has_more = Boolean(response.hasMore);
                stage.total_cards = response.total ?? stage.total_cards;
            } catch (e) {
                this.error = 'Erro ao carregar mais cards.';
            } finally {
                stage.loadingMore = false;
                this.$nextTick(() => this.initSortables());
            }
        },

        initSortables() {
            if (typeof Sortable === 'undefined') {
                return;
            }

            this.destroySortables();

            this.$el.querySelectorAll('[data-stage-id]').forEach((column) => {
                const instance = Sortable.create(column, {
                    group: 'kanban-cards',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    dragClass: 'shadow-2xl',
                    chosenClass: 'ring-2',
                    onEnd: async (event) => {
                        const cardId = Number(event.item.dataset.cardId);
                        const toStageId = Number(event.to.dataset.stageId);
                        const fromStageId = Number(event.from.dataset.stageId);

                        if (!cardId || !toStageId) {
                            return;
                        }

                        if (toStageId === fromStageId && event.oldIndex === event.newIndex) {
                            return;
                        }

                        try {
                            const payload = await wire.moveCard(cardId, toStageId, event.newIndex, this.cardsPerStage);
                            this.applyBoard(payload);
                        } catch (e) {
                            this.error = 'Erro ao mover card. Recarregando board...';
                            await this.fetchBoard();
                        } finally {
                            this.$nextTick(() => this.initSortables());
                        }
                    },
                });

                this.sortables.push(instance);
            });
        },

        destroySortables() {
            this.sortables.forEach((instance) => instance.destroy());
            this.sortables = [];
        },

        conversationUrl(card) {
            if (!this.activeFunnel) {
                return '#';
            }

            return `${this.chatwootBaseUrl}/app/accounts/${this.activeFunnel.account_id}/inbox/${card.inbox_id}/conversations/${card.conversation_id}`;
        },

        priorityClass(priority) {
            if (priority === 'urgent') {
                return 'bg-green-100 text-green-700 dark:bg-green-700 dark:text-green-300';
            }

            if (priority === 'high') {
                return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-700 dark:text-yellow-300';
            }

            if (priority === 'medium') {
                return 'bg-red-100 text-red-700 dark:bg-red-700 dark:text-red-300';
            }

            return 'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-zinc-300';
        },

        async deleteCard(cardId) {
            if (!window.confirm('Excluir card?')) {
                return;
            }

            try {
                const payload = await wire.removeCard(cardId, this.cardsPerStage);
                this.applyBoard(payload);
            } catch (e) {
                this.error = 'Erro ao excluir card.';
            } finally {
                this.$nextTick(() => this.initSortables());
            }
        },

        async deleteStage(stageId) {
            if (!window.confirm('Excluir etapa?')) {
                return;
            }

            try {
                const payload = await wire.removeStage(stageId, this.cardsPerStage);
                this.applyBoard(payload);
            } catch (e) {
                this.error = 'Erro ao excluir etapa.';
            } finally {
                this.$nextTick(() => this.initSortables());
            }
        },

        async deleteFunnel() {
            if (!this.activeFunnelId) {
                return;
            }

            if (!window.confirm('Tem certeza que deseja excluir este funil?')) {
                return;
            }

            try {
                const payload = await wire.removeFunnel(this.activeFunnelId, this.cardsPerStage);
                this.applyBoard(payload);
            } catch (e) {
                this.error = 'Erro ao excluir funil.';
            } finally {
                this.$nextTick(() => this.initSortables());
            }
        },
    }));
});
</script>