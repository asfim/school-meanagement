<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

interface CampusLifeItem {
    id: number;
    title: string;
    description: string | null;
    image_path: string | null;
    sort_order: number;
    is_active: boolean;
}

const props = defineProps<{ items: CampusLifeItem[] }>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Campus Life Gallery', href: '/campus-life' },
];

function deleteItem(id: number) {
    Swal.fire({
        title: 'Delete this item?',
        text: 'It will be removed from the homepage gallery.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#171717',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/campus-life/${id}`);
        }
    });
}
</script>

<template>
    <Head title="Campus Life Gallery" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">Campus Life Gallery</h1>
                    <p class="mt-1 text-sm text-neutral-500">Manage gallery items shown on the homepage "Campus Life" section.</p>
                </div>
                <Link
                    href="/campus-life/create"
                    class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 shadow transition"
                >
                    + Add Gallery Item
                </Link>
            </div>

            <!-- Empty State -->
            <div v-if="items.length === 0" class="rounded-xl border border-dashed border-neutral-200 dark:border-neutral-800 p-12 text-center text-neutral-500">
                No gallery items yet. Click "Add Gallery Item" to create one.
            </div>

            <!-- Item Cards -->
            <div class="grid grid-cols-1 gap-5">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl overflow-hidden shadow-sm flex flex-col sm:flex-row"
                >
                    <!-- Image preview -->
                    <div class="w-full sm:w-48 h-40 sm:h-auto shrink-0 bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center overflow-hidden">
                        <img
                            v-if="item.image_path"
                            :src="`/storage/${item.image_path}`"
                            :alt="item.title"
                            class="w-full h-full object-cover"
                        />
                        <span v-else class="text-xs text-neutral-400 px-3 text-center">No image</span>
                    </div>

                    <div class="flex-1 p-5 space-y-2">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-xs font-mono px-2 py-0.5 rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400">
                                #{{ item.sort_order }}
                            </span>
                            <span
                                class="text-xs font-bold px-2 py-0.5 rounded"
                                :class="item.is_active ? 'bg-green-50 text-green-700' : 'bg-neutral-100 text-neutral-500'"
                            >
                                {{ item.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <h3 class="text-lg font-extrabold text-neutral-900 dark:text-neutral-50">{{ item.title }}</h3>
                        <p v-if="item.description" class="text-sm text-neutral-500 line-clamp-2">{{ item.description }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex sm:flex-col items-center justify-end gap-2 p-4 border-t sm:border-t-0 sm:border-l border-neutral-100 dark:border-neutral-800">
                        <Link
                            :href="`/campus-life/${item.id}/edit`"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 transition"
                        >
                            Edit
                        </Link>
                        <button
                            @click="deleteItem(item.id)"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg bg-red-50 hover:bg-red-100 text-red-700 transition"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tip -->
            <div class="text-xs text-neutral-400 bg-neutral-50 dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-800 rounded-lg p-3">
                💡 Tip: Use Sort Order (0, 1, 2...) to control the display order on the homepage gallery. Toggle Active to show/hide items.
            </div>
        </div>
    </AppLayout>
</template>
