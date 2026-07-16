<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

interface Teacher {
    id: number;
    teacher_id: string;
    full_name: string;
    designation: string;
    email: string;
    mobile: string;
    subjects: string[];
    program_names: string[];
    photo_path?: string | null;
}

const props = defineProps<{
    teachers: Teacher[];
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Teachers', href: '/teachers' },
];

function applyFilters() {
    router.get('/teachers', {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function refreshSearch() {
    search.value = '';
    applyFilters();
}

function deleteTeacher(id: number) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Are you sure you want to remove this teacher record? This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#171717',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/teachers/${id}`);
        }
    });
}
</script>

<template>
    <Head title="Faculty Directory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">Faculty Directory</h1>
                    <p class="mt-1 text-sm text-neutral-500">Manage academic teachers, subject assignments, and attendance logs.</p>
                </div>
                <div class="flex gap-2">
                    <Link
                        href="/teachers/attendance"
                        class="inline-flex items-center justify-center rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white hover:bg-neutral-50 dark:bg-neutral-900 dark:hover:bg-neutral-800 px-4 py-2.5 text-sm font-semibold text-neutral-700 dark:text-neutral-300 shadow-sm transition"
                    >
                        Mark Today's Attendance
                    </Link>
                    <Link
                        href="/teachers/create"
                        class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200 shadow transition"
                    >
                        Add New Teacher
                    </Link>
                </div>
            </div>

            <!-- Filters Toolbar -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4 shadow-sm flex flex-col sm:flex-row items-center gap-4">
                <div class="flex-1 w-full flex gap-2">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by name, ID, email..."
                        class="flex-1 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-955 dark:focus:ring-neutral-300"
                        @keyup.enter="applyFilters"
                    />
                    <button
                        @click="refreshSearch"
                        type="button"
                        class="px-3 py-2 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-sm font-semibold rounded-lg text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-700 transition"
                    >
                        Clear
                    </button>
                </div>
                <button
                    @click="applyFilters"
                    class="px-4 py-2 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-sm font-semibold rounded-lg text-neutral-900 dark:text-neutral-100 w-full sm:w-auto"
                >
                    Search Faculty
                </button>
            </div>

            <!-- Faculty Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="teacher in teachers"
                    :key="teacher.id"
                    class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm flex flex-col justify-between hover:shadow-md transition"
                >
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <img
                                v-if="teacher.photo_path"
                                :src="'/storage/' + teacher.photo_path"
                                class="w-14 h-14 rounded-full object-cover border border-neutral-200 dark:border-neutral-800 shadow-sm"
                                alt="Teacher photo"
                            />
                            <div
                                v-else
                                class="w-14 h-14 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-xl border border-neutral-200 dark:border-neutral-800"
                            >
                                👤
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-bold text-neutral-400 font-mono tracking-widest block">{{ teacher.teacher_id }}</span>
                                <h3 class="text-lg font-bold text-neutral-950 dark:text-neutral-50 mt-0.5 truncate">{{ teacher.full_name }}</h3>
                                <p class="text-xs text-neutral-500 font-semibold mt-0.5">{{ teacher.designation }}</p>
                            </div>
                        </div>

                        <div class="text-sm space-y-1.5 text-neutral-600 dark:text-neutral-400">
                            <div><span class="text-neutral-400">Email:</span> <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ teacher.email }}</span></div>
                            <div><span class="text-neutral-400">Mobile:</span> <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ teacher.mobile }}</span></div>
                        </div>

                        <!-- Program -->
                        <div v-if="teacher.program_names && teacher.program_names.length > 0">
                            <span class="text-xs font-semibold text-neutral-400 block mb-1">Program</span>
                            <div class="flex flex-wrap gap-1">
                                <span v-for="prog in teacher.program_names" :key="prog" class="inline-flex px-2 py-0.5 rounded text-xs bg-neutral-200 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 font-medium">
                                    {{ prog }}
                                </span>
                            </div>
                        </div>

                        <!-- Assigned Subjects -->
                        <div>
                            <span class="text-xs font-semibold text-neutral-400 block mb-1">Subjects Taught</span>
                            <div class="flex flex-wrap gap-1">
                                <span v-for="sub in teacher.subjects" :key="sub" class="inline-flex px-2 py-0.5 rounded text-xs bg-neutral-100 dark:bg-neutral-850 text-neutral-700 dark:text-neutral-900 font-medium">
                                    {{ sub }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3 text-sm">
                        <Link :href="'/teachers/' + teacher.id + '/edit'" class="text-neutral-900 dark:text-neutral-100 hover:underline font-semibold">Edit</Link>
                        <button @click="deleteTeacher(teacher.id)" class="text-red-600 hover:text-red-800">Delete</button>
                    </div>
                </div>

                <div v-if="teachers.length === 0" class="col-span-full py-12 text-center text-neutral-500">
                    No faculty staff registered yet.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
