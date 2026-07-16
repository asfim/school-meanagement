<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface ResultHistoryItem {
    id: number;
    exam_name: string;
    semester_name: string;
    semester_id: number;
    marks: Record<string, number>;
    total_marks: number;
    percentage: number;
    gpa: string;
    grade: string;
    pass_status: 'pass' | 'fail';
    date: string;
}

const props = defineProps<{
    student: {
        id: number;
        student_id: string;
        full_name_en: string;
        roll_number: number;
        section: string;
        program_name: string;
        semester?: {
            name: string;
        };
    };
    groupedHistory: Record<string, ResultHistoryItem[]>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Academic Results', href: '/results' },
    { title: 'Student History', href: '#' },
];
</script>

<template>
    <Head :title="student.full_name_en + ' - Academic History'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-5xl mx-auto space-y-6">
            <!-- Student Header Information -->
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4 animate-in fade-in duration-200">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-neutral-450 uppercase tracking-widest block">Student Profile</span>
                    <h1 class="text-2xl font-black text-neutral-900 dark:text-neutral-50">{{ student.full_name_en }}</h1>
                    <p class="text-sm text-neutral-500">{{ student.program_name }} &bull; Section {{ student.section }} &bull; Roll {{ student.roll_number }}</p>
                </div>
                <div class="flex flex-col md:items-end gap-1">
                    <span class="text-xs text-neutral-450 font-medium">Student ID: <span class="font-mono font-bold text-neutral-905 dark:text-neutral-100">{{ student.student_id }}</span></span>
                    <span class="text-xs text-neutral-450 font-medium">Current Semester: <span class="font-semibold text-neutral-905 dark:text-neutral-100">{{ student.semester?.name || 'N/A' }}</span></span>
                </div>
            </div>

            <!-- Semesters Results Listing -->
            <div class="space-y-8">
                <div v-for="(exams, semesterName) in groupedHistory" :key="semesterName" class="space-y-4">
                    <h2 class="text-lg font-black text-neutral-900 dark:text-neutral-50 border-b border-neutral-200 dark:border-neutral-800 pb-2 flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        {{ semesterName }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="exam in exams" :key="exam.id" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-5 shadow-sm space-y-4 flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-neutral-900 dark:text-neutral-50">{{ exam.exam_name }}</h3>
                                    <span v-if="exam.pass_status === 'pass'" class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">Passed</span>
                                    <span v-else class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">Failed</span>
                                </div>

                                <!-- Subject-wise table -->
                                <div class="border border-neutral-100 dark:border-neutral-800 rounded-lg overflow-hidden">
                                    <table class="w-full text-left text-xs border-collapse">
                                        <thead>
                                            <tr class="bg-neutral-50 dark:bg-neutral-950 text-neutral-500 font-semibold border-b border-neutral-100 dark:border-neutral-800">
                                                <th class="p-2">Subject</th>
                                                <th class="p-2 text-right">Marks</th>
                                                <th class="p-2 text-center">Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800 text-neutral-700 dark:text-neutral-350">
                                            <tr v-for="(marks, subject) in exam.marks" :key="subject">
                                                <td class="p-2">{{ subject }}</td>
                                                <td class="p-2 text-right font-mono font-bold">{{ marks }}</td>
                                                <td class="p-2 text-center font-bold">
                                                    <span :class="marks >= 33 ? 'text-neutral-900 dark:text-neutral-200' : 'text-red-500'">
                                                        {{ marks >= 80 ? 'A+' : (marks >= 70 ? 'A' : (marks >= 60 ? 'A-' : (marks >= 50 ? 'B' : (marks >= 40 ? 'C' : (marks >= 33 ? 'D' : 'F'))))) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Performance Metrics Summary Footer -->
                            <div class="bg-neutral-50 dark:bg-neutral-950/40 p-3 rounded-lg border border-neutral-100 dark:border-neutral-800 grid grid-cols-3 gap-2 text-center">
                                <div>
                                    <span class="text-[9px] font-bold text-neutral-450 uppercase block">Total / %</span>
                                    <span class="text-xs font-black text-neutral-900 dark:text-neutral-50 font-mono">{{ exam.total_marks }} ({{ exam.percentage }}%)</span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-bold text-neutral-450 uppercase block">GPA Score</span>
                                    <span class="text-xs font-black text-indigo-650 dark:text-indigo-400 font-mono">{{ exam.gpa }}</span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-bold text-neutral-450 uppercase block">Overall Grade</span>
                                    <span class="text-xs font-black font-mono" :class="exam.grade === 'F' ? 'text-red-500' : 'text-green-600 dark:text-green-450'">{{ exam.grade }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="Object.keys(groupedHistory).length === 0" class="bg-white dark:bg-neutral-900 border border-dashed border-neutral-200 dark:border-neutral-800 p-12 rounded-xl text-center text-neutral-500">
                No exam results recorded for this student yet.
            </div>
        </div>
    </AppLayout>
</template>
