import Sortable from 'sortablejs';
import { registerKanbanBoard } from './components/kanban-board';
import { registerNotify } from './components/notify';
import { registerSearchableConversationSelect } from './components/searchable-conversation-select';

// Expose Sortable globally so inline Livewire component scripts can use it
window.Sortable = Sortable;

registerKanbanBoard();
registerNotify();
registerSearchableConversationSelect();

// import Alpine from 'alpinejs';

// window.Alpine = Alpine;

// Alpine.start();
