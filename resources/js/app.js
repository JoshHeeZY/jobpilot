import Sortable from 'sortablejs';

/**
 * Turn a board column into a drag-and-drop list.
 *
 * When a card is dropped we put it straight back where it came from and let
 * Livewire re-render the board from the database. That way the server is the
 * single source of truth and Livewire never fights SortableJS over the DOM.
 */
window.makeSortable = (column, $wire) => {
    let originalNextSibling = null;

    Sortable.create(column, {
        group: 'board', // cards can move between any columns in this group
        draggable: '[data-id]',
        animation: 150,
        ghostClass: 'opacity-40',

        onStart: (event) => {
            originalNextSibling = event.item.nextSibling;
        },

        onEnd: (event) => {
            const id = event.item.dataset.id;
            const status = event.to.dataset.status;
            const orderedIds = [...event.to.querySelectorAll('[data-id]')].map((card) => card.dataset.id);

            // Undo the DOM move; Livewire will draw the card in its new place.
            event.from.insertBefore(event.item, originalNextSibling);

            $wire.move(id, status, orderedIds);
        },
    });
};
