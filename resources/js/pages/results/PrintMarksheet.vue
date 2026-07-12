<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface Student {
    id: number;
    student_id: string;
    full_name_en: string;
    full_name_native: string;
    class: string;
    section: string;
    roll_number: number;
}

interface ExamResult {
    id: number;
    exam_name: string;
    class: string;
    section: string;
    marks: Record<string, number>;
    gpa: string;
    grade: string;
    pass_status: 'pass' | 'fail';
    remarks: string | null;
    student: Student;
}

const props = defineProps<{
    result: ExamResult;
}>();

function printResult() {
    window.print();
}

function getSubjectGp(score: number): number {
    if (score >= 80) return 5.0;
    if (score >= 70) return 4.0;
    if (score >= 60) return 3.5;
    if (score >= 50) return 3.0;
    if (score >= 40) return 2.0;
    if (score >= 33) return 1.0;
    return 0.0;
}

function getSubjectGrade(score: number): string {
    if (score >= 80) return 'A+';
    if (score >= 70) return 'A';
    if (score >= 60) return 'A-';
    if (score >= 50) return 'B';
    if (score >= 40) return 'C';
    if (score >= 33) return 'D';
    return 'F';
}
</script>

<template>
    <Head :title="`Marksheet - ${result.student.full_name_en}`" />

    <div class="min-h-screen bg-neutral-100 dark:bg-neutral-950 p-6 flex flex-col items-center print:bg-white print:p-0">
        <!-- Control buttons (hidden on print) -->
        <div class="mb-6 w-full max-w-2xl flex justify-between items-center print:hidden">
            <Link
                :href="'/results?class=' + result.class + '&section=' + result.section + '&exam_name=' + result.exam_name"
                class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 hover:bg-neutral-50 rounded-lg text-sm font-semibold text-neutral-700 dark:text-neutral-300"
            >
                &larr; Back to Results
            </Link>
            <button
                @click="printResult"
                class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 hover:bg-neutral-800 text-white rounded-lg text-sm font-semibold shadow"
            >
                Print Report Card
            </button>
        </div>

        <!-- Official Report Card Layout -->
        <div class="w-full max-w-2xl bg-white border border-neutral-200 p-8 rounded-xl shadow-sm text-neutral-900 font-sans print:shadow-none print:border-none print:p-0">
            <!-- School Head -->
            <div class="text-center border-b-2 border-neutral-900 pb-4">
                <h1 class="text-2xl font-black uppercase tracking-wider">Antigravity Model School</h1>
                <p class="text-xs uppercase tracking-widest text-neutral-500 mt-1">Official Progress Report</p>
            </div>

            <!-- Student Bio Information -->
            <div class="grid grid-cols-2 gap-4 my-6 text-sm">
                <div>
                    <div><span class="text-neutral-500">Student Name:</span> <span class="font-bold">{{ result.student.full_name_en }}</span></div>
                    <div><span class="text-neutral-500">Student ID:</span> <span class="font-bold font-mono">{{ result.student.student_id }}</span></div>
                    <div><span class="text-neutral-500">Class & Section:</span> <span class="font-bold">{{ result.class }} (Sec {{ result.section }})</span></div>
                </div>
                <div class="text-right">
                    <div><span class="text-neutral-500">Exam:</span> <span class="font-bold">{{ result.exam_name }}</span></div>
                    <div><span class="text-neutral-500">Roll Number:</span> <span class="font-bold">#{{ result.student.roll_number }}</span></div>
                    <div><span class="text-neutral-500">Date Issued:</span> <span class="font-bold">{{ new Date().toLocaleDateString() }}</span></div>
                </div>
            </div>

            <!-- Subject-wise Marks Table -->
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-neutral-100 border-y border-neutral-300 font-bold">
                        <th class="p-3">Subject Name</th>
                        <th class="p-3 text-center w-24">Marks Obtained</th>
                        <th class="p-3 text-center w-28">Grade Point</th>
                        <th class="p-3 text-center w-24">Grade Letter</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    <tr v-for="(score, subject) in result.marks" :key="subject">
                        <td class="p-3 font-semibold text-neutral-800">{{ subject }}</td>
                        <td class="p-3 text-center font-bold">{{ score }}</td>
                        <td class="p-3 text-center font-mono font-medium">{{ getSubjectGp(score).toFixed(2) }}</td>
                        <td class="p-3 text-center font-bold" :class="score < 33 ? 'text-red-650' : ''">{{ getSubjectGrade(score) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Summary metrics -->
            <div class="border-t border-neutral-300 mt-6 pt-4 grid grid-cols-2 gap-4">
                <div class="space-y-1.5 text-sm">
                    <div><span class="text-neutral-500">Result Status:</span> 
                        <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded text-xs font-bold" :class="result.pass_status === 'pass' ? 'bg-green-150 text-green-800' : 'bg-red-150 text-red-800'">
                            {{ result.pass_status === 'pass' ? 'Passed' : 'Failed' }}
                        </span>
                    </div>
                    <div><span class="text-neutral-500">Remarks:</span> <span class="italic font-medium ml-1.5">{{ result.remarks || 'None.' }}</span></div>
                </div>

                <div class="text-right border-l border-neutral-200 pl-4 space-y-1.5">
                    <div class="text-sm"><span class="text-neutral-500">Grade Point Average (GPA):</span> <span class="font-extrabold text-lg ml-1 font-mono">{{ result.gpa }}</span></div>
                    <div class="text-sm"><span class="text-neutral-500">Overall Grade:</span> <span class="font-black text-xl ml-1 text-neutral-950">{{ result.grade }}</span></div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="flex justify-between items-end mt-16 text-sm">
                <div>
                    <p class="border-t border-neutral-400 pt-2 px-4 text-center">Class Teacher</p>
                </div>
                <div>
                    <p class="border-t border-neutral-400 pt-2 px-4 text-center">Controller of Exams</p>
                </div>
            </div>
        </div>
    </div>
</template>
