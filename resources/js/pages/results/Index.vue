<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface ReportCardRow {
    student_id: number;
    student_uid: string;
    full_name: string;
    roll_number: number;
    has_result: boolean;
    result_id: number | null;
    gpa: string | null;
    grade: string | null;
    pass_status: 'pass' | 'fail' | null;
}

interface LinkItem {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedReportCard {
    data: ReportCardRow[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: LinkItem[];
}

const props = defineProps<{
    reportCard: PaginatedReportCard;
    programs: string[];
    sections: string[];
    examNames: string[];
    currentFilters: {
        program_name: string;
        section: string;
        exam_name: string;
        search?: string;
    };
}>();

const selectedProgram = ref(props.currentFilters.program_name);
const selectedSection = ref(props.currentFilters.section);
const selectedExam = ref(props.currentFilters.exam_name);
const search = ref(props.currentFilters.search || '');

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Academic Results', href: '/results' },
];

function applyFilters() {
    router.get('/results', {
        program_name: selectedProgram.value,
        section: selectedSection.value,
        exam_name: selectedExam.value,
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

// Watch filters to reload page
watch([selectedProgram, selectedSection, selectedExam, search], () => {
    applyFilters();
});
</script>

<template>
    <Head title="Academic Results Tabulation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">Academic Results Tabulation</h1>
                    <p class="mt-1 text-sm text-neutral-500">Record examination score logs, compute class-wise GPA summary and print marksheet cards.</p>
                </div>
                <div>
                    <Link
                        :href="'/results/marks-entry?program_name=' + selectedProgram + '&section=' + selectedSection + '&exam_name=' + selectedExam"
                        class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200 shadow transition"
                    >
                        Enter Program Marks
                    </Link>
                </div>
            </div>

            <!-- Filters Toolbar -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4 shadow-sm grid grid-cols-1 sm:grid-cols-4 gap-4 animate-in fade-in slide-in-from-top-1 duration-200">
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 mb-1">Search Student</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by Roll, Name, ID..."
                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-450 focus:outline-none focus:ring-1 focus:ring-neutral-900 dark:focus:ring-neutral-300"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 mb-1">Select Exam *</label>
                    <select v-model="selectedExam" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                        <option v-for="e in examNames" :key="e" :value="e">{{ e }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 mb-1">Select Program *</label>
                    <select v-model="selectedProgram" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                        <option v-for="p in programs" :key="p" :value="p">{{ p }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 mb-1">Select Section *</label>
                    <select v-model="selectedSection" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                        <option v-for="s in sections" :key="s" :value="s">Section {{ s }}</option>
                    </select>
                </div>
            </div>

            <!-- Tabulation Card -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-950 text-neutral-500 dark:text-neutral-400 font-semibold border-b border-neutral-200 dark:border-neutral-800">
                                <th class="p-4">Sl</th>
                                <th class="p-4">Roll</th>
                                <th class="p-4">Student ID</th>
                                <th class="p-4">Full Name</th>
                                <th class="p-4 text-center">Result Status</th>
                                <th class="p-4 text-center">GPA Score</th>
                                <th class="p-4 text-center">Grade Letter</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="(row, index) in reportCard.data" :key="row.student_id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-950/30 text-neutral-700 dark:text-neutral-300">
                                <td class="p-4">{{ (reportCard.current_page - 1) * 15 + index + 1 }}</td>
                                <td class="p-4 text-neutral-950 dark:text-neutral-100 font-bold">{{ row.roll_number }}</td>
                                <td class="p-4 font-mono text-xs font-medium">{{ row.student_uid }}</td>
                                <td class="p-4 font-semibold text-neutral-950 dark:text-neutral-100">{{ row.full_name }}</td>
                                <td class="p-4 text-center">
                                    <span v-if="row.has_result && row.pass_status === 'pass'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-450 border border-green-200 dark:border-green-800">Passed</span>
                                    <span v-else-if="row.has_result && row.pass_status === 'fail'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400 border border-red-200 dark:border-red-800">Failed</span>
                                    <span v-else class="text-xs text-neutral-400">No score entered</span>
                                </td>
                                <td class="p-4 text-center font-mono font-bold">{{ row.gpa || '-' }}</td>
                                <td class="p-4 text-center font-bold text-lg" :class="row.grade === 'F' ? 'text-red-500' : 'text-neutral-100 dark:text-neutral-250'">{{ row.grade || '-' }}</td>
                                <td class="p-4 text-right space-x-2">
                                    <template v-if="row.has_result">
                                        <Link
                                            :href="`/results/${row.result_id}/print`"
                                            class="px-3 py-1 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-xs font-bold rounded-lg text-neutral-900 dark:text-neutral-100 transition"
                                        >
                                            Print Marksheet
                                        </Link>
                                        <Link
                                            :href="'/results/marks-entry?program_name=' + selectedProgram + '&section=' + selectedSection + '&exam_name=' + selectedExam"
                                            class="text-xs text-indigo-650 hover:underline"
                                        >
                                            Edit Marks
                                        </Link>
                                    </template>
                                    <template v-else>
                                        <Link
                                            :href="'/results/marks-entry?program_name=' + selectedProgram + '&section=' + selectedSection + '&exam_name=' + selectedExam"
                                            class="px-2.5 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800 text-xs font-bold rounded-lg transition"
                                        >
                                            Enter Marks
                                        </Link>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="reportCard.data.length === 0">
                                <td colspan="8" class="p-8 text-center text-neutral-500">No students enrolled matching filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="reportCard.last_page > 1" class="p-4 bg-neutral-50 dark:bg-neutral-950 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <div class="text-xs text-neutral-500">Page {{ reportCard.current_page }} of {{ reportCard.last_page }}</div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in reportCard.links"
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
