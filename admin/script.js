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
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');

    if (modal && openModalBtn && closeModalBtn) {
        openModalBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });

        closeModalBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }

    // Modal Edit
    const editModal = document.getElementById('edit-modal');
    const editCloseBtn = document.getElementById('edit-close-btn');
    const editButtons = document.querySelectorAll('.edit-btn');

    if (editModal && editCloseBtn && editButtons) {
        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const fields = JSON.parse(button.dataset.fields);
                console.log('Data dari tombol:', fields);

                // Isi form berdasarkan data yang diambil
                const editForm = document.getElementById('edit-form');
                if (editForm) {
                    Object.keys(fields).forEach(key => {
                        const inputElement = editForm.querySelector(`#edit-${key}`);
                        if (inputElement) {
                            inputElement.value = fields[key];
                        }
                    });
                    // Set action form jika diperlukan
                    editForm.setAttribute('action', `${window.location.pathname}?id=${button.dataset.id}`);
                }

                // Tampilkan modal
                editModal.classList.remove('hidden');
            });
        });

        // Tutup modal
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
                const url = button.getAttribute('data-url');

                deleteConfirmBtn.setAttribute('data-url', url);
                deleteConfirmBtn.setAttribute('data-id', id);
                deleteModal.classList.remove('hidden');
            });
        });

        deleteCancelBtn.addEventListener('click', () => {
            deleteModal.classList.add('hidden');
        });

        deleteConfirmBtn.addEventListener('click', () => {
            const url = deleteConfirmBtn.getAttribute('data-url');
            if (url) {
                window.location.href = url; // Redirect ke URL untuk penghapusan
            }
        });
    }
});
