export function registerSearchableConversationSelect() {
    const register = () => {
        if (!window.Alpine || window.__searchableConversationSelectRegistered) {
            return !!window.Alpine;
        }

        window.__searchableConversationSelectRegistered = true;

        window.Alpine.data('searchableConversationSelect', () => ({
            open: false,
            highlightedIndex: 0,

            openList() {
                this.open = true;
                this.highlightedIndex = 0;
            },

            closeList() {
                this.open = false;
                this.highlightedIndex = 0;
            },

            handleInput() {
                this.$wire.$set('selectedConversationId', null);
                this.open = true;
                this.highlightedIndex = 0;
            },

            moveHighlight(step) {
                const options = this.optionElements();

                if (options.length === 0) {
                    return;
                }

                if (!this.open) {
                    this.openList();

                    return;
                }

                const nextIndex = this.highlightedIndex + step;
                this.highlightedIndex = Math.max(0, Math.min(nextIndex, options.length - 1));
                options[this.highlightedIndex]?.scrollIntoView({ block: 'nearest' });
            },

            selectHighlighted() {
                const option = this.optionElements()[this.highlightedIndex];

                option?.click();
            },

            selectOption(conversationId, label) {
                this.$wire.$set('selectedConversationId', conversationId);
                this.$wire.$set('filterCard', label);
                this.closeList();
                this.$nextTick(() => this.$refs.searchInput?.blur());
            },

            optionElements() {
                return Array.from(this.$refs.options?.querySelectorAll('[data-option]') ?? []);
            },
        }));

        return true;
    };

    if (!register()) {
        document.addEventListener('alpine:init', register);
    }
}