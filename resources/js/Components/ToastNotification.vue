<script setup>
import { watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const page = usePage();

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    customClass: {
        popup: 'sharp-toast',
        title: 'sharp-toast-title',
        htmlContainer: 'sharp-toast-content',
        timerProgressBar: 'sharp-toast-progress',
    },
    showClass: {
        popup: 'animate__animated animate__fadeInRight animate__faster'
    },
    hideClass: {
        popup: 'animate__animated animate__fadeOutRight animate__faster'
    },
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

const showNotification = () => {
    const flash = page.props.flash;

    if (flash.success) {
        Toast.fire({
            icon: 'success',
            title: 'Success',
            text: flash.success,
        });
    }

    if (flash.error) {
        Toast.fire({
            icon: 'error',
            title: 'Action Restricted',
            text: flash.error,
        });
    }

    if (flash.status) {
        Toast.fire({
            icon: 'info',
            title: 'Status Update',
            text: flash.status,
        });
    }
};

// Watch for flash message changes
watch(
    () => page.props.flash,
    () => {
        showNotification();
    },
    { deep: true }
);

// Check on initial load
onMounted(() => {
    showNotification();
});
</script>

<template>
    <!-- Hidden element just to mount -->
    <div v-if="false"></div>
</template>

<style>
.sharp-toast {
    background: #ffffff !important;
    border: 1px solid #e5e7eb !important;
    border-left: 5px solid #6366f1 !important;
    border-radius: 4px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    padding: 0.75rem 1rem !important;
}

/* Dynamic Left Border Colors */
.sharp-toast.swal2-icon-success { border-left-color: #10b981 !important; }
.sharp-toast.swal2-icon-error { border-left-color: #ef4444 !important; }
.sharp-toast.swal2-icon-info { border-left-color: #3b82f6 !important; }
.sharp-toast.swal2-icon-warning { border-left-color: #f59e0b !important; }

.sharp-toast-title {
    color: #111827 !important;
    font-size: 0.95rem !important;
    font-weight: 800 !important;
    margin: 0 !important;
    text-align: left !important;
    font-family: 'Inter', system-ui, sans-serif !important;
}

.sharp-toast-content {
    font-size: 0.85rem !important;
    color: #4b5563 !important;
    margin: 0.2rem 0 0 0 !important;
    text-align: left !important;
    font-weight: 500 !important;
}

.sharp-toast-progress {
    background: rgba(0, 0, 0, 0.04) !important;
    height: 3px !important;
}

/* Remove default Swall icon margin */
.swal2-icon {
    margin: 0 0.75rem 0 0 !important;
    transform: scale(0.8);
}
</style>
