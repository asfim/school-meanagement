<script setup lang="ts">
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const page = usePage();

watch(
    () => page.props.flash,
    (newFlash: any) => {
        if (!newFlash) return;

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            customClass: {
                popup: 'swal2-toast-custom border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-50 shadow-md rounded-xl font-sans',
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        if (newFlash.success) {
            Toast.fire({
                icon: 'success',
                title: newFlash.success,
            });
        } else if (newFlash.error) {
            Toast.fire({
                icon: 'error',
                title: newFlash.error,
            });
        } else if (newFlash.warning) {
            Toast.fire({
                icon: 'warning',
                title: newFlash.warning,
            });
        } else if (newFlash.info) {
            Toast.fire({
                icon: 'info',
                title: newFlash.info,
            });
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <!-- Invisible helper component -->
</template>

<style>
/* Custom styled SweetAlert override to match the project's aesthetics */
.swal2-toast-custom {
    padding: 0.75rem 1rem !important;
    display: flex !important;
    align-items: center !important;
}
.swal2-toast-custom .swal2-title {
    font-size: 0.875rem !important;
    font-weight: 600 !important;
    color: inherit !important;
    margin: 0 0 0 0.5rem !important;
}
.swal2-toast-custom .swal2-timer-progress-bar {
    background: rgba(0, 0, 0, 0.1) !important;
}
.dark .swal2-toast-custom .swal2-timer-progress-bar {
    background: rgba(255, 255, 255, 0.2) !important;
}
</style>
