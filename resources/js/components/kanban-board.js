export function registerKanbanBoard() {
    const register = () => {
        if (!window.Alpine || window.__kanbanBoardAlpineRegistered) {
            return !!window.Alpine;
        }

        window.__kanbanBoardAlpineRegistered = true;

        window.Alpine.data('kanbanBoard', (wire) => ({
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
            boardRoot: null,
            listeners: [],
            chatwootBaseUrl: '',

            async init() {
                this.boardRoot = this.$refs.boardRoot ?? this.$el;
                this.chatwootBaseUrl = this.$el.dataset.chatwootBaseUrl ?? '';

                await this.fetchBoard();

                this.listeners = [
                    Livewire.on('funnel-saved', async () => await this.fetchBoard()),
                    Livewire.on('stage-saved', async () => await this.fetchBoard()),
                    Livewire.on('card-moved', async () => await this.fetchBoard()),
                ];
            },

            destroy() {
                this.listeners.forEach((stop) => stop());
                this.listeners = [];
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
                }
            },

            initSortables() {
                if (typeof Sortable === 'undefined') {
                    console.error('SortableJS is not loaded. Please ensure it is included in your project.');
                    return;
                }

                this.destroySortables();

                const boardElement = this.boardRoot ?? this.$refs.boardRoot ?? this.$el;
                if (!boardElement) {
                    console.error('Board root element not found.');
                    return;
                }

                boardElement.querySelectorAll('[data-stage-id]').forEach((column) => {
                    const instance = Sortable.create(column, {
                        group: 'kanban-cards',
                        animation: 150,
                        ghostClass: 'opacity-40',
                        dragClass: 'shadow-2xl',
                        chosenClass: 'kanban-card-chosen',
                        filter: '.no-drag',
                        onEnd: async (event) => {
                            const cardId = Number(event.item.dataset.cardId);
                            const toStageId = Number(event.to.dataset.stageId);
                            const fromStageId = Number(event.from.dataset.stageId);

                            if (!cardId || !toStageId) {
                                return;
                            }

                            if (toStageId === fromStageId) {
                                return;
                            }

                            try {
                                const payload = await wire.moveCard(cardId, toStageId, event.newIndex, this.cardsPerStage);
                                this.applyBoard(payload);
                            } catch (e) {
                                this.error = 'Erro ao mover card. Recarregando board...';
                                await this.fetchBoard();
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
                if (!this.activeFunnel || !this.chatwootBaseUrl) {
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
                }
            },
        }));

        return true;
    };

    if (!register()) {
        document.addEventListener('alpine:init', register);
    }
}