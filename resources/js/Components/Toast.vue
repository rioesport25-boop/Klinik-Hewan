<script setup>
import { watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const page = usePage();

const showToast = (msg, toastType = 'success') => {
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

    Toast.fire({
        icon: toastType,
        title: msg
    });
};

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        showToast(flash.success, 'success');
    } else if (flash?.error) {
        showToast(flash.error, 'error');
    } else if (flash?.warning) {
        showToast(flash.warning, 'warning');
    } else if (flash?.info) {
        showToast(flash.info, 'info');
    }
}, { deep: true, immediate: true });

onMounted(() => {
    // Check for initial flash messages
    const flash = page.props.flash;
    if (flash?.success) {
        showToast(flash.success, 'success');
    } else if (flash?.error) {
        showToast(flash.error, 'error');
    } else if (flash?.warning) {
        showToast(flash.warning, 'warning');
    } else if (flash?.info) {
        showToast(flash.info, 'info');
    }
});
</script>

<template>
    <!-- SweetAlert2 handles the toast display automatically -->
</template>
