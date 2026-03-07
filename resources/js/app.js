import Sortable from 'sortablejs';
import { registerKanbanBoard } from './components/kanban-board';
import { registerNotify } from './components/notify';

// Expose Sortable globally so inline Livewire component scripts can use it
window.Sortable = Sortable;

registerKanbanBoard();
registerNotify();

// import Alpine from 'alpinejs';

// window.Alpine = Alpine;

// Alpine.start();
