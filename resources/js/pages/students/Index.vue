<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

interface Student {
    id: number;
    student_id: string;
    full_name_en: string;
    full_name_native: string;
    class: string;
    section: string;
    roll_number: number;
    parent_mobile: string;
    status: 'active' | 'transferred' | 'inactive';
}

const props = defineProps<{
    students: {
        data: Student[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        search?: string;
        class?: string;
        section?: string;
    };
    classes: string[];
    sections: string[];
}>();

const search = ref(props.filters.search || '');
const selectedClass = ref(props.filters.class || '');
const selectedSection = ref(props.filters.section || '');

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
];

function applyFilters() {
    router.get('/students', {
        search: search.value,
        class: selectedClass.value,
        section: selectedSection.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

// Watch filters to trigger automatically
watch([selectedClass, selectedSection], () => {
    applyFilters();
});

function deleteStudent(id: number) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Are you sure you want to delete this student record? This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#171717',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/students/${id}`);
        }
    });
}

function issueTc(id: number) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Are you sure you want to issue a Transfer Certificate (TC) for this student?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#171717',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, issue it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(`/students/${id}/issue-tc`);
        }
    });
}
</script>

<template>
    <Head title="Students Directory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">Students Directory</h1>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Manage student enrollment, profiles, and issue transfer certificates.</p>
                </div>
                <div>
                    <Link
                        href="/students/create"
                        class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200 shadow transition"
                    >
                        Register New Student
                    </Link>
                </div>
            </div>

            <!-- Filters Toolbar -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4 shadow-sm flex flex-col md:flex-row md:items-center gap-4">
                <div class="flex-1">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by name, Student ID, roll number..."
                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-950 dark:focus:ring-neutral-300"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <div class="flex gap-4">
                    <select
                        v-model="selectedClass"
                        class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2"
                    >
                        <option value="">All Classes</option>
                        <option v-for="c in classes" :key="c" :value="c">{{ c }}</option>
                    </select>
                    <select
                        v-model="selectedSection"
                        class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2"
                    >
                        <option value="">All Sections</option>
                        <option v-for="s in sections" :key="s" :value="s">Section {{ s }}</option>
                    </select>
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-sm font-semibold rounded-lg text-neutral-900 dark:text-neutral-100"
                    >
                        Search
                    </button>
                </div>
            </div>

            <!-- Students Table -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-950 text-neutral-500 dark:text-neutral-400 font-semibold border-b border-neutral-200 dark:border-neutral-800">
                                <th class="p-4">Student ID</th>
                                <th class="p-4">Full Name</th>
                                <th class="p-4">Class & Sec</th>
                                <th class="p-4 text-center">Roll</th>
                                <th class="p-4">Parent Mobile</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="student in students.data" :key="student.id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-950/30 text-neutral-700 dark:text-neutral-300">
                                <td class="p-4 font-mono font-medium text-neutral-950 dark:text-neutral-100">{{ student.student_id }}</td>
                                <td class="p-4">
                                    <div class="font-semibold text-neutral-950 dark:text-neutral-100">{{ student.full_name_en }}</div>
                                    <div class="text-xs text-neutral-500">{{ student.full_name_native }}</div>
                                </td>
                                <td class="p-4">{{ student.class }} ({{ student.section }})</td>
                                <td class="p-4 text-center">{{ student.roll_number }}</td>
                                <td class="p-4">{{ student.parent_mobile }}</td>
                                <td class="p-4">
                                    <span v-if="student.status === 'active'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400 border border-green-200 dark:border-green-800">Active</span>
                                    <span v-else-if="student.status === 'transferred'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800">Transferred</span>
                                    <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-neutral-50 text-neutral-700 dark:bg-neutral-950/30 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-800">Inactive</span>
                                </td>
                                <td class="p-4 text-right space-x-2">
                                    <Link :href="'/students/' + student.id" class="text-neutral-900 dark:text-neutral-100 hover:underline font-semibold">View</Link>
                                    <Link :href="'/students/' + student.id + '/edit'" class="text-neutral-600 dark:text-neutral-400 hover:underline">Edit</Link>
                                    <button
                                        v-if="student.status === 'active'"
                                        @click="issueTc(student.id)"
                                        class="text-amber-600 hover:text-amber-800 font-semibold"
                                    >
                                        TC
                                    </button>
                                    <button @click="deleteStudent(student.id)" class="text-red-600 hover:text-red-800">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="students.data.length === 0">
                                <td colspan="7" class="p-8 text-center text-neutral-500">No students found matching filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="students.last_page > 1" class="p-4 bg-neutral-50 dark:bg-neutral-950 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <div class="text-xs text-neutral-500">Page {{ students.current_page }} of {{ students.last_page }}</div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in students.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 text-xs rounded border transition',
                                link.active ? 'bg-neutral-950 text-white border-neutral-950 dark:bg-neutral-50 dark:text-neutral-950' : 'bg-white hover:bg-neutral-100 text-neutral-700 border-neutral-300 dark:bg-neutral-900 dark:hover:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
