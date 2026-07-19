<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

interface Notice {
    id: number;
    title: string;
    description: string;
    category: 'exam' | 'holiday' | 'event' | 'general' | 'admission' | 'urgent';
    publish_date: string;
    expiry_date: string | null;
    target_audience: 'all' | 'students' | 'teachers' | 'parents';
    status: 'active' | 'inactive' | 'draft';
    posted_by: string;
}

const props = defineProps<{
    notices: Notice[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Notice Board', href: '/notices' },
];

function deleteNotice(id: number) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Are you sure you want to delete this notice? It will disappear from the homepage.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#171717',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/notices/${id}`);
        }
    });
}
</script>

<template>
    <Head title="Notice Board Center" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">Notice Board Control Center</h1>
                    <p class="mt-1 text-sm text-neutral-500">Draft, schedule, and publish official school notices to the public homepage portal.</p>
                </div>
                <div>
                    <Link
                        href="/notices/create"
                        class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200 shadow transition"
                    >
                        Create New Notice
                    </Link>
                </div>
            </div>

            <!-- Notice Grid -->
            <div class="grid grid-cols-1 gap-6">
                <div
                    v-for="notice in notices"
                    :key="notice.id"
                    class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm space-y-4 hover:shadow-md transition"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-neutral-100 dark:border-neutral-800 pb-3">
                        <div class="flex items-center gap-3">
                            <!-- Category Badge -->
                            <span
                                class="inline-flex px-2.5 py-0.5 rounded text-xs font-black uppercase tracking-wider"
                                :class="{
                                    'bg-red-50 text-red-700 border border-red-200': notice.category === 'urgent',
                                    'bg-blue-50 text-blue-700 border border-blue-200': notice.category === 'exam',
                                    'bg-green-50 text-green-700 border border-green-200': notice.category === 'holiday',
                                    'bg-purple-50 text-purple-700 border border-purple-200': notice.category === 'event',
                                    'bg-amber-50 text-amber-700 border border-amber-200': notice.category === 'admission',
                                    'bg-neutral-50 text-neutral-700 border border-neutral-200': notice.category === 'general',
                                }"
                            >
                                {{ notice.category }}
                            </span>
                            <h3 class="text-lg font-extrabold text-neutral-950 dark:text-neutral-50">{{ notice.title }}</h3>
                        </div>

                        <!-- Info details -->
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-neutral-400">
                            <div>Published: <span class="font-bold text-neutral-600 dark:text-neutral-350">{{ new Date(notice.publish_date).toLocaleDateString() }}</span></div>
                            <div v-if="notice.expiry_date">Expires: <span class="font-bold text-neutral-650">{{ new Date(notice.expiry_date).toLocaleDateString() }}</span></div>
                            <div>Audience: <span class="font-bold capitalize text-neutral-650">{{ notice.target_audience }}</span></div>
                            <div>
                                <span v-if="notice.status === 'active'" class="inline-flex px-1.5 py-0.2 rounded text-[10px] bg-green-50 text-green-700">Active</span>
                                <span v-else-if="notice.status === 'draft'" class="inline-flex px-1.5 py-0.2 rounded text-[10px] bg-neutral-100 text-neutral-650">Draft</span>
                                <span v-else class="inline-flex px-1.5 py-0.2 rounded text-[10px] bg-red-50 text-red-700">Inactive</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <p class="text-sm text-neutral-700 dark:text-neutral-300 leading-relaxed whitespace-pre-wrap">
                        {{ notice.description.length > 120 ? notice.description.substring(0, 120) + '...' : notice.description }}
                        <Link v-if="notice.description.length > 120" :href="`/notice/${notice.slug}`" class="text-neutral-950 dark:text-neutral-50 font-bold hover:underline ml-1">
                            Read More &rarr;
                        </Link>
                    </p>

                    <!-- Signatures / Footer -->
                    <div class="flex items-center justify-between text-xs text-neutral-500 pt-3 border-t border-neutral-50 dark:border-neutral-850">
                        <div>Posted by: <span class="font-bold text-neutral-750">{{ ['Exam Controller', 'Principal Office', 'Academic Coordinator', 'Admissions Office', 'Admin Office'].includes(notice.posted_by) ? notice.posted_by : 'Admin' }}</span></div>
                        <div class="flex gap-3">
                            <Link :href="`/notices/${notice.id}/edit`" class="text-neutral-900 dark:text-neutral-100 hover:underline font-bold">Edit Notice</Link>
                            <button @click="deleteNotice(notice.id)" class="text-red-650 hover:underline">Delete</button>
                        </div>
                    </div>
                </div>

                <div v-if="notices.length === 0" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-12 text-center text-neutral-500">
                    No notices have been published.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
