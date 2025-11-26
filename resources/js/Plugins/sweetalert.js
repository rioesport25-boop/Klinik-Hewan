import Swal from 'sweetalert2';

// Default configuration
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

// Success notification
export const showSuccess = (message, title = 'Berhasil!') => {
    return Toast.fire({
        icon: 'success',
        title: title,
        text: message
    });
};

// Error notification
export const showError = (message, title = 'Terjadi Kesalahan!') => {
    return Toast.fire({
        icon: 'error',
        title: title,
        text: message
    });
};

// Warning notification
export const showWarning = (message, title = 'Peringatan!') => {
    return Toast.fire({
        icon: 'warning',
        title: title,
        text: message
    });
};

// Info notification
export const showInfo = (message, title = 'Info') => {
    return Toast.fire({
        icon: 'info',
        title: title,
        text: message
    });
};

// Confirmation dialog
export const showConfirm = (options = {}) => {
    const defaultOptions = {
        title: 'Apakah Anda yakin?',
        text: 'Tindakan ini tidak dapat dibatalkan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, lanjutkan!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    };

    return Swal.fire({ ...defaultOptions, ...options });
};

// Loading dialog
export const showLoading = (message = 'Memproses...') => {
    return Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

// Close any open Swal
export const closeSwal = () => {
    Swal.close();
};

// Custom alert
export const showAlert = (options) => {
    return Swal.fire(options);
};

export default {
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showConfirm,
    showLoading,
    closeSwal,
    showAlert,
    Swal
};
