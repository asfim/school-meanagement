<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

interface Banner {
    id: number;
    title: string;
    subtitle: string | null;
    paragraph: string | null;
    button_text: string | null;
    button_url: string | null;
    bg_color: 'forest' | 'ink' | 'brass';
    sort_order: number;
    is_active: boolean;
}

defineProps<{ banners: Banner[] }>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Hero Banners', href: '/banners' },
];

const bgLabels: Record<string, string> = {
    forest: '🌿 Forest Green',
    ink: '🌑 Dark Ink',
    brass: '🟡 Brass Gold',
};

function deleteBanner(id: number) {
    Swal.fire({
        title: 'Delete Banner?',
        text: 'This banner will be removed from the homepage slider.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#171717',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/banners/${id}`);
        }
    });
}
</script>

<template>
    <Head title="Hero Banners" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">Hero Banners</h1>
                    <p class="mt-1 text-sm text-neutral-500">Manage the homepage hero slider. Banners are shown in sort order.</p>
                </div>
                <Link
                    href="/banners/create"
                    class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 shadow transition"
                >
                    + Add Banner
                </Link>
            </div>

            <!-- Empty State -->
            <div v-if="banners.length === 0" class="rounded-xl border border-dashed border-neutral-200 dark:border-neutral-800 p-12 text-center text-neutral-500">
                No banners created yet. Click "Add Banner" to create one.
            </div>

            <!-- Banner Cards -->
            <div class="grid grid-cols-1 gap-5">
                <div
                    v-for="banner in banners"
                    :key="banner.id"
                    class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl overflow-hidden shadow-sm flex flex-col sm:flex-row"
                >
                    <!-- Color preview strip -->
                    <div
                        class="w-full sm:w-3 shrink-0"
                        :class="{
                            'bg-[#1F4D3A]': banner.bg_color === 'forest',
                            'bg-[#14213D]': banner.bg_color === 'ink',
                            'bg-[#C89B3C]': banner.bg_color === 'brass',
                        }"
                    ></div>

                    <div class="flex-1 p-5 space-y-2">
                        <!-- Top row -->
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-xs font-mono px-2 py-0.5 rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400">
                                #{{ banner.sort_order }} · {{ bgLabels[banner.bg_color] }}
                            </span>
                            <span
                                class="text-xs font-bold px-2 py-0.5 rounded"
                                :class="banner.is_active ? 'bg-green-50 text-green-700' : 'bg-neutral-100 text-neutral-500'"
                            >
                                {{ banner.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <h3 class="text-lg font-extrabold text-neutral-900 dark:text-neutral-50">{{ banner.title }}</h3>
                        <p v-if="banner.subtitle" class="text-sm font-medium text-neutral-600 dark:text-neutral-400">{{ banner.subtitle }}</p>
                        <p v-if="banner.paragraph" class="text-sm text-neutral-500 line-clamp-2">{{ banner.paragraph }}</p>
                        <p v-if="banner.button_text" class="text-xs text-neutral-400">
                            Button: <span class="font-semibold text-neutral-600 dark:text-neutral-300">{{ banner.button_text }}</span>
                            <span v-if="banner.button_url"> → {{ banner.button_url }}</span>
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex sm:flex-col items-center justify-end gap-2 p-4 border-t sm:border-t-0 sm:border-l border-neutral-100 dark:border-neutral-800">
                        <Link
                            :href="`/banners/${banner.id}/edit`"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 transition"
                        >
                            Edit
                        </Link>
                        <button
                            @click="deleteBanner(banner.id)"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg bg-red-50 hover:bg-red-100 text-red-700 transition"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tip -->
            <div class="text-xs text-neutral-400 bg-neutral-50 dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-800 rounded-lg p-3">
                💡 Tip: Set Sort Order (0, 1, 2...) to control which banner appears first in the slider.
            </div>
        </div>
    </AppLayout>
</template>
