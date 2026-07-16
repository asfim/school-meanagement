<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

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
    is_final: boolean;
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

const cgpa = computed(() => {
    const semesters = Object.keys(props.groupedHistory);
    const semesterCount = semesters.length;

    if (semesterCount >= 8) {
        // Calculate average of the "Final Semester Exam" of each of the semesters
        let totalFinalGpa = 0;
        let finalExamCount = 0;

        Object.values(props.groupedHistory).forEach(exams => {
            // Find the exam marked as final
            const finalExam = exams.find(e => e.is_final);
            if (finalExam) {
                const val = parseFloat(finalExam.gpa);
                if (!isNaN(val)) {
                    totalFinalGpa += val;
                    finalExamCount++;
                }
            } else {
                // Fallback to the latest exam of the semester if no final exam exists
                if (exams.length > 0) {
                    const lastExam = exams[exams.length - 1];
                    const val = parseFloat(lastExam.gpa);
                    if (!isNaN(val)) {
                        totalFinalGpa += val;
                        finalExamCount++;
                    }
                }
            }
        });

        return finalExamCount > 0 ? (totalFinalGpa / finalExamCount).toFixed(2) : '0.00';
    } else {
        // Show last exam's GPA
        const allExams = Object.values(props.groupedHistory).flat();
        if (allExams.length === 0) return '0.00';

        // Sort by ID to find the actual last exam
        const sortedExams = [...allExams].sort((a, b) => a.id - b.id);
        const lastExam = sortedExams[sortedExams.length - 1];

        return parseFloat(lastExam.gpa).toFixed(2);
    }
});

const cgpaGrade = computed(() => {
    const score = parseFloat(cgpa.value);
    if (isNaN(score) || score <= 0) return 'N/A';
    if (score >= 5.0) return 'A+';
    if (score >= 4.0) return 'A';
    if (score >= 3.5) return 'A-';
    if (score >= 3.0) return 'B';
    if (score >= 2.0) return 'C';
    if (score >= 1.0) return 'D';
    return 'F';
});

function printHistory() {
    window.print();
}
</script>

<template>
    <Head :title="student.full_name_en + ' - Academic History'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-5xl mx-auto space-y-6">
            <!-- Navigation and Control Bar -->
            <div class="flex items-center justify-between print:hidden">
                <Link
                    href="/results"
                    class="inline-flex items-center text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200 transition"
                >
                    &larr; Back to Results
                </Link>
                <button
                    @click="printHistory"
                    class="inline-flex items-center justify-center rounded-lg bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-200 text-white px-4 py-2.5 text-xs font-bold shadow transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.844l-.326-2.12m11.21 2.12l.326-2.12m-11.536 0L8.72 5.844A2.25 2.25 0 0110.92 4h2.16a2.25 2.25 0 012.2 1.844l.326 2.12m-11.537 0h11.537m-11.537 0H4.75A2.25 2.25 0 002.5 10v6a2.25 2.25 0 002.25 2.25h14.5A2.25 2.25 0 0021.5 16v-6a2.25 2.25 0 00-2.25-2.25h-.92m-11.536 0h11.536M9 20h6M9 16h6" />
                    </svg>
                    Print Academic History
                </button>
            </div>

            <!-- Student Header Information -->
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4 animate-in fade-in duration-200">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-neutral-450 uppercase tracking-widest block">Student Profile</span>
                    <h1 class="text-2xl font-black text-neutral-900 dark:text-neutral-50">{{ student.full_name_en }}</h1>
                    <p class="text-sm text-neutral-500">{{ student.program_name }} &bull; Section {{ student.section }} &bull; Roll {{ student.roll_number }}</p>
                </div>
                <div class="flex flex-row md:flex-col items-center md:items-end justify-between md:justify-start gap-4 md:gap-1.5 border-t md:border-t-0 pt-4 md:pt-0 border-neutral-150 dark:border-neutral-850">
                    <div class="text-left md:text-right space-y-1">
                        <div class="text-xs text-neutral-450 font-medium">Student ID: <span class="font-mono font-bold text-neutral-905 dark:text-neutral-100">{{ student.student_id }}</span></div>
                        <div class="text-xs text-neutral-450 font-medium">Current Semester: <span class="font-semibold text-neutral-905 dark:text-neutral-100">{{ student.semester?.name || 'N/A' }}</span></div>
                    </div>
                    <div class="border-l md:border-l-0 md:border-t border-neutral-200 dark:border-neutral-800 pl-4 md:pl-0 md:pt-1.5 flex items-center gap-2">
                        <span class="text-xs font-bold text-neutral-450 uppercase tracking-wider">CGPA:</span>
                        <span class="text-lg font-black font-mono text-indigo-650 dark:text-indigo-400">{{ cgpa }}</span>
                        <span class="text-xs font-bold px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">{{ cgpaGrade }}</span>
                    </div>
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

<style>
@media print {
    /* Hide layout sidebar, header, triggers, and control buttons */
    header,
    aside,
    [data-sidebar="sidebar"],
    .peer,
    .group.peer,
    [data-collapsible],
    .print\:hidden {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }

    /* Reset global page backgrounds and layout structures */
    html, body {
        background-color: white !important;
        color: black !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Override wrapper margins, flex constraints, and paddings */
    .flex.min-h-svh,
    .flex-1,
    main,
    .max-w-5xl,
    .p-6 {
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        box-shadow: none !important;
        max-width: 100% !important;
        width: 100% !important;
        display: block !important;
    }

    /* Space out semester blocks in print view */
    .space-y-8 > * + * {
        margin-top: 2.5rem !important;
    }

    /* Keep exam cards in a two-column grid on print if width allows */
    .grid {
        display: grid !important;
        grid-template-cols: repeat(2, minmax(0, 1fr)) !important;
        gap: 1.5rem !important;
    }

    /* Clean card style for print format */
    .bg-white,
    .dark\:bg-neutral-900 {
        background-color: white !important;
        color: black !important;
        border: 1px solid #e5e7eb !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }

    /* Readable color settings on paper */
    .text-neutral-500,
    .text-neutral-450 {
        color: #4b5563 !important;
    }

    .text-neutral-900,
    .dark\:text-neutral-50 {
        color: #000000 !important;
    }

    .bg-neutral-50,
    .dark\:bg-neutral-950\/40 {
        background-color: #f3f4f6 !important;
        color: #111827 !important;
    }
}
</style>
