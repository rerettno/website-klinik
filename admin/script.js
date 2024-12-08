document.addEventListener('DOMContentLoaded', function () {
    // Drawer Menu
    const drawer = document.getElementById('drawer-navigation');
    const overlay = document.getElementById('drawer-overlay');
    const toggleButton = document.querySelector('[data-drawer-target="drawer-navigation"]');

    if (drawer && overlay && toggleButton) {
        // Toggle Drawer
        toggleButton.addEventListener('click', function () {
            drawer.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        // Close Drawer on Overlay Click
        overlay.addEventListener('click', function () {
            drawer.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }

    // Modal Popup for Add
    const modal = document.getElementById('modal');
    const modalOverlay = document.getElementById('modal-overlay');
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');

    if (modal && openModalBtn && closeModalBtn) {
        openModalBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
            modalOverlay.classList.remove('hidden');
        });

        closeModalBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
            modalOverlay.classList.add('hidden');
        });
    }

    // Modal Popup for Edit
    const editModal = document.getElementById('edit-modal');
    const editCloseBtn = document.getElementById('edit-close-btn');
    const editButtons = document.querySelectorAll('.edit-btn');

    if (editModal && editCloseBtn && editButtons) {
        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Fetch data from the row (optional)
                const row = button.closest('tr');
                const name = row.querySelector('.poli-name').textContent.trim();
                const description = row.querySelector('.poli-description').textContent.trim();

                // Populate modal inputs
                document.getElementById('edit-name').value = name;
                document.getElementById('edit-description').value = description;

                // Show modal
                editModal.classList.remove('hidden');
            });
        });

        // Close modal
        editCloseBtn.addEventListener('click', () => {
            editModal.classList.add('hidden');
        });
    }

    // Modal Popup for Delete
    const deleteModal = document.getElementById('delete-modal');
    const deleteCancelBtn = document.getElementById('delete-cancel-btn');
    const deleteConfirmBtn = document.getElementById('delete-confirm-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');

    let rowToDelete = null; // Store row to delete

    if (deleteModal && deleteCancelBtn && deleteConfirmBtn && deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Store row to delete
                rowToDelete = button.closest('tr');
                // Show modal
                deleteModal.classList.remove('hidden');
            });
        });

        // Cancel delete
        deleteCancelBtn.addEventListener('click', () => {
            deleteModal.classList.add('hidden');
            rowToDelete = null;
        });

        // Confirm delete
        deleteConfirmBtn.addEventListener('click', () => {
            if (rowToDelete) {
                rowToDelete.remove(); // Remove the row from table
                rowToDelete = null;
            }
            deleteModal.classList.add('hidden');
        });
    }
});
