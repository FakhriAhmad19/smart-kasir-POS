import * as bootstrap from 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import Swal from 'sweetalert2';
import '../css/app.css';

window.bootstrap = bootstrap;
window.Swal = Swal;

// Global SweetAlert confirmation for any form with class "js-confirm-delete"
document.addEventListener('submit', function (e) {
    const form = e.target.closest('form.js-confirm-delete');
    if (!form) return;

    e.preventDefault();

    Swal.fire({
        icon: 'warning',
        title: form.dataset.confirmTitle || 'Konfirmasi Hapus Data',
        text: form.dataset.confirmText || 'Data yang dihapus tidak dapat dikembalikan lagi. Lanjutkan?',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
    }).then(function (result) {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
