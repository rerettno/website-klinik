document.addEventListener('DOMContentLoaded', function () {
    // Flash Message
    const flashMessage = document.getElementById('flash-message');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.transition = 'opacity 0.5s';
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.remove(), 500);
        }, 2000);
    }

    // Modal Tambah
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

    // Modal Edit
    const editModal = document.getElementById('edit-modal');
    const editCloseBtn = document.getElementById('edit-close-btn');
    const editButtons = document.querySelectorAll('.edit-btn');

    if (editModal && editCloseBtn && editButtons) {
        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const row = button.closest('tr');
                const name = row.querySelector('.poli-name').textContent.trim();
                const description = row.querySelector('.poli-keterangan').textContent.trim();

                document.getElementById('edit-name').value = name;
                document.getElementById('edit-description').value = description;

                const id = button.getAttribute('data-id');
                const editForm = document.getElementById('edit-form');
                editForm.setAttribute('action', `poli.php?id=${id}`);

                editModal.classList.remove('hidden');
            });
        });

        editCloseBtn.addEventListener('click', () => {
            editModal.classList.add('hidden');
        });
    }

    // Modal Hapus
    const deleteModal = document.getElementById('delete-modal');
    const deleteCancelBtn = document.getElementById('delete-cancel-btn');
    const deleteConfirmBtn = document.getElementById('delete-confirm-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');

    if (deleteModal && deleteCancelBtn && deleteConfirmBtn && deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                deleteConfirmBtn.setAttribute('data-id', id);
                deleteModal.classList.remove('hidden');
            });
        });

        deleteCancelBtn.addEventListener('click', () => {
            deleteModal.classList.add('hidden');
        });

        deleteConfirmBtn.addEventListener('click', () => {
            const id = deleteConfirmBtn.getAttribute('data-id');
            if (id) {
                window.location.href = `delete_poli.php?id=${id}`;
            }
        });
    }
});

