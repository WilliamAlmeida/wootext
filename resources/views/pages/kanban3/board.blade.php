<div>
    <livewire:kanban3.board />

    @island(defer: true)
        <livewire:components.kanban.card-detail />
        <livewire:components.kanban.board-modals />
        <livewire:components.kanban.schedule-messages-modal />
    @endisland
</div>
