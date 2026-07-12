<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Notice {
    id: number;
    title: string;
    description: string;
    category: 'exam' | 'holiday' | 'event' | 'general' | 'admission' | 'urgent';
    publish_date: string;
    posted_by: string;
}

interface Student {
    student_id: string;
    full_name_en: string;
    class: string;
    section: string;
    roll_number: number;
}

interface ExamResult {
    exam_name: string;
    marks: Record<string, number>;
    gpa: string;
    grade: string;
    pass_status: 'pass' | 'fail';
    remarks: string | null;
}

interface FeePayment {
    fee_month: string;
    amount_due: string;
    amount_paid: string;
    discount: string;
    status: 'paid' | 'partial' | 'unpaid';
    receipt_number: string | null;
    payment_date: string | null;
}

const props = defineProps<{
    notices: Notice[];
    resultData: { student: Student; result: ExamResult } | null;
    resultError: string | null;
    feeData: { student: Student; payments: FeePayment[] } | null;
    feeError: string | null;
    filters: {
        result_student_id?: string;
        result_exam_name?: string;
        fee_student_id?: string;
    };
}>();

const activeTab = ref('notices'); // 'notices', 'results', 'fees'

// Forms for queries
const resultForm = useForm({
    result_student_id: props.filters.result_student_id || '',
    result_exam_name: props.filters.result_exam_name || 'First Term Exam',
});

const feeForm = useForm({
    fee_student_id: props.filters.fee_student_id || '',
});

function submitResultQuery() {
    resultForm.get('/', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            activeTab.value = 'results';
        }
    });
}

function submitFeeQuery() {
    feeForm.get('/', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            activeTab.value = 'fees';
        }
    });
}

function printCard(elementId: string) {
    const printContent = document.getElementById(elementId)?.innerHTML;
    const originalContent = document.body.innerHTML;
    if (printContent) {
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        window.location.reload(); // reload to restore bindings
    }
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
    <Head title="Welcome to Antigravity Model School" />

    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a] text-neutral-800 dark:text-neutral-250 font-sans flex flex-col justify-between">
        <!-- Main Navbar -->
        <header class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 py-4 px-6 sticky top-0 z-40">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg bg-neutral-950 dark:bg-neutral-50 flex items-center justify-center font-black text-white dark:text-neutral-950 tracking-wider">
                        AG
                    </div>
                    <span class="font-black tracking-wide text-lg text-neutral-950 dark:text-neutral-50 uppercase">Antigravity School</span>
                </div>
                <nav class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 bg-white hover:bg-neutral-50 rounded-lg text-sm font-semibold text-neutral-700 dark:bg-neutral-900 dark:text-neutral-200"
                    >
                        Go to Dashboard
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="px-4 py-2 bg-neutral-950 hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200 text-white rounded-lg text-sm font-semibold shadow transition"
                    >
                        Staff Login
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Hero Branding -->
        <div class="bg-gradient-to-br from-neutral-950 via-neutral-900 to-neutral-800 py-16 text-center text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
            <div class="max-w-3xl mx-auto space-y-4 px-6 relative z-10">
                <h1 class="text-4xl sm:text-5xl font-black uppercase tracking-wider">Shaping future Leaders</h1>
                <p class="text-sm sm:text-base text-neutral-400 max-w-xl mx-auto">Welcome to the Antigravity Model School digital portal. Access notice boards, lookup student marks sheets, and verify pending tuition fees.</p>
            </div>
        </div>

        <!-- Portal Hub Tabs -->
        <main class="max-w-6xl mx-auto w-full px-6 py-12 flex-grow">
            <!-- Tabs selection -->
            <div class="flex border-b border-neutral-200 dark:border-neutral-800 gap-6 mb-8 text-sm sm:text-base font-bold">
                <button
                    @click="activeTab = 'notices'"
                    class="pb-3 border-b-2 transition"
                    :class="activeTab === 'notices' ? 'border-neutral-950 text-neutral-950 dark:border-neutral-50 dark:text-neutral-50' : 'border-transparent text-neutral-450 hover:text-neutral-700'"
                >
                    Notice Board
                </button>
                <button
                    @click="activeTab = 'results'"
                    class="pb-3 border-b-2 transition"
                    :class="activeTab === 'results' ? 'border-neutral-950 text-neutral-950 dark:border-neutral-50 dark:text-neutral-50' : 'border-transparent text-neutral-450 hover:text-neutral-700'"
                >
                    Student Result Portal
                </button>
                <button
                    @click="activeTab = 'fees'"
                    class="pb-3 border-b-2 transition"
                    :class="activeTab === 'fees' ? 'border-neutral-950 text-neutral-950 dark:border-neutral-50 dark:text-neutral-50' : 'border-transparent text-neutral-450 hover:text-neutral-700'"
                >
                    Tuition Status Portal
                </button>
            </div>

            <!-- Tab Content 1: Notice Board -->
            <div v-if="activeTab === 'notices'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        v-for="notice in notices"
                        :key="notice.id"
                        class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-xs flex flex-col justify-between hover:shadow-md transition"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
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
                                <span class="text-xs text-neutral-400">{{ new Date(notice.publish_date).toLocaleDateString() }}</span>
                            </div>
                            <h3 class="text-lg font-extrabold text-neutral-950 dark:text-neutral-50 leading-snug">{{ notice.title }}</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">{{ notice.description }}</p>
                        </div>
                        <div class="text-[10px] text-neutral-400 pt-3 border-t border-neutral-100 dark:border-neutral-800 mt-4">
                            Posted by: <span class="font-bold text-neutral-600 dark:text-neutral-305">{{ notice.posted_by || 'Academic Registry' }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="notices.length === 0" class="text-center py-12 text-neutral-500">
                    No active notices published at the moment.
                </div>
            </div>

            <!-- Tab Content 2: Student Result Portal -->
            <div v-if="activeTab === 'results'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Search panel -->
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm h-fit">
                    <h3 class="text-lg font-bold mb-4">Lookup Report Card</h3>
                    <form @submit.prevent="submitResultQuery" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Student ID *</label>
                            <input
                                v-model="resultForm.result_student_id"
                                type="text"
                                placeholder="STU-2026-0001"
                                class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Select Exam *</label>
                            <select v-model="resultForm.result_exam_name" class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm focus:outline-none">
                                <option value="First Term Exam">First Term Exam</option>
                                <option value="Midterm Exam">Midterm Exam</option>
                                <option value="Annual Exam">Annual Exam</option>
                            </select>
                        </div>
                        <button type="submit" :disabled="resultForm.processing" class="w-full py-2 bg-neutral-950 hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200 text-white rounded text-sm font-semibold shadow">
                            Search Result
                        </button>
                    </form>
                    <div v-if="resultError" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded">
                        {{ resultError }}
                    </div>
                </div>

                <!-- Marksheet Display -->
                <div class="lg:col-span-2 space-y-4">
                    <div v-if="resultData" class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-sm font-bold text-neutral-450 uppercase tracking-wider">Result Details</h4>
                            <button
                                @click="printCard('printable-report-card')"
                                class="px-3 py-1.5 bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold rounded"
                            >
                                Print Report Card
                            </button>
                        </div>

                        <!-- Printable wrapper -->
                        <div id="printable-report-card" class="bg-white p-6 rounded-lg text-neutral-900 border border-neutral-100 font-sans">
                            <!-- Header -->
                            <div class="text-center border-b-2 border-neutral-900 pb-3">
                                <h2 class="text-xl font-black uppercase tracking-wider">Antigravity Model School</h2>
                                <p class="text-[9px] uppercase tracking-widest text-neutral-500">Student Academic mark Sheet</p>
                            </div>

                            <!-- Bio -->
                            <div class="grid grid-cols-2 gap-2 my-4 text-xs">
                                <div>
                                    <div>Student: <span class="font-bold">{{ resultData.student.full_name_en }}</span></div>
                                    <div>Student ID: <span class="font-bold font-mono">{{ resultData.student.student_id }}</span></div>
                                    <div>Class: <span class="font-semibold">{{ resultData.student.class }}</span></div>
                                </div>
                                <div class="text-right">
                                    <div>Exam: <span class="font-bold">{{ resultData.result.exam_name }}</span></div>
                                    <div>Roll Number: <span class="font-semibold">#{{ resultData.student.roll_number }}</span></div>
                                    <div>Status: 
                                        <span class="ml-1 inline-flex px-1.5 py-0.2 rounded text-[10px] font-bold" :class="resultData.result.pass_status === 'pass' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                            {{ resultData.result.pass_status.toUpperCase() }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Subject table -->
                            <table class="w-full text-left border-collapse text-xs mt-4">
                                <thead>
                                    <tr class="bg-neutral-100 border-y border-neutral-300 font-bold">
                                        <th class="p-2">Subject Name</th>
                                        <th class="p-2 text-center w-20">Score</th>
                                        <th class="p-2 text-center w-24">Grade Point</th>
                                        <th class="p-2 text-center w-20">Grade</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200">
                                    <tr v-for="(score, subject) in resultData.result.marks" :key="subject">
                                        <td class="p-2 font-medium text-neutral-800">{{ subject }}</td>
                                        <td class="p-2 text-center font-bold">{{ score }}</td>
                                        <td class="p-2 text-center font-mono">{{ getSubjectGp(score).toFixed(2) }}</td>
                                        <td class="p-2 text-center font-bold" :class="score < 33 ? 'text-red-650' : ''">{{ getSubjectGrade(score) }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Summary -->
                            <div class="border-t border-neutral-300 mt-4 pt-3 flex justify-between items-center text-xs">
                                <div>
                                    <div>Remarks: <span class="italic font-medium">{{ resultData.result.remarks || 'Satisfactory.' }}</span></div>
                                </div>
                                <div class="text-right space-y-1">
                                    <div>GPA: <span class="font-extrabold text-sm font-mono">{{ resultData.result.gpa }}</span></div>
                                    <div>Final Grade: <span class="font-black text-base">{{ resultData.result.grade }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="h-64 border border-dashed border-neutral-200 dark:border-neutral-800 rounded-xl flex items-center justify-center text-neutral-400">
                        Please search for a student's ID above to display report card.
                    </div>
                </div>
            </div>

            <!-- Tab Content 3: Tuition Status Portal -->
            <div v-if="activeTab === 'fees'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Search panel -->
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm h-fit">
                    <h3 class="text-lg font-bold mb-4">Tuition Fee Inquiry</h3>
                    <form @submit.prevent="submitFeeQuery" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Student ID *</label>
                            <input
                                v-model="feeForm.fee_student_id"
                                type="text"
                                placeholder="STU-2026-0001"
                                class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                            />
                        </div>
                        <button type="submit" :disabled="feeForm.processing" class="w-full py-2 bg-neutral-950 hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200 text-white rounded text-sm font-semibold shadow">
                            Query Billings
                        </button>
                    </form>
                    <div v-if="feeError" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded">
                        {{ feeError }}
                    </div>
                </div>

                <!-- Fee Billings Display -->
                <div class="lg:col-span-2 space-y-4">
                    <div v-if="feeData" class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h4 class="font-bold text-neutral-950">Tuition Statement</h4>
                                <p class="text-xs text-neutral-500">Student: {{ feeData.student.full_name_en }} ({{ feeData.student.student_id }})</p>
                            </div>
                            <button
                                @click="printCard('printable-fee-statement')"
                                class="px-3 py-1.5 bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold rounded"
                            >
                                Print Statement
                            </button>
                        </div>

                        <!-- Printable Statement wrapper -->
                        <div id="printable-fee-statement" class="bg-white p-6 rounded-lg text-neutral-900 border border-neutral-100 font-sans">
                            <div class="text-center border-b border-neutral-200 pb-3 mb-4">
                                <h2 class="text-xl font-black uppercase tracking-wider">Antigravity Model School</h2>
                                <p class="text-[9px] uppercase tracking-widest text-neutral-500">Tuition Fee Statement Ledger</p>
                            </div>

                            <div class="grid grid-cols-2 gap-2 my-2 text-xs">
                                <div>
                                    <div>Student Name: <span class="font-bold">{{ feeData.student.full_name_en }}</span></div>
                                    <div>Class: <span class="font-semibold">{{ feeData.student.class }}</span></div>
                                </div>
                                <div class="text-right">
                                    <div>Student ID: <span class="font-bold font-mono">{{ feeData.student.student_id }}</span></div>
                                    <div>Roll Number: <span class="font-semibold">#{{ feeData.student.roll_number }}</span></div>
                                </div>
                            </div>

                            <table class="w-full text-left border-collapse text-xs mt-4">
                                <thead>
                                    <tr class="bg-neutral-100 border-y border-neutral-300 font-bold">
                                        <th class="p-2">Billing Month</th>
                                        <th class="p-2 text-center">Amount Due</th>
                                        <th class="p-2 text-center">Amount Paid</th>
                                        <th class="p-2 text-center">Discount</th>
                                        <th class="p-2 text-center">Status</th>
                                        <th class="p-2 text-right">Receipt #</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-200">
                                    <tr v-for="pay in feeData.payments" :key="pay.fee_month">
                                        <td class="p-2 font-medium">{{ pay.fee_month }}</td>
                                        <td class="p-2 text-center font-bold">${{ parseFloat(pay.amount_due).toFixed(2) }}</td>
                                        <td class="p-2 text-center text-green-700 font-semibold">${{ parseFloat(pay.amount_paid).toFixed(2) }}</td>
                                        <td class="p-2 text-center text-amber-500">${{ parseFloat(pay.discount).toFixed(2) }}</td>
                                        <td class="p-2 text-center">
                                            <span v-if="pay.status === 'paid'" class="text-[10px] font-bold text-green-700">Paid</span>
                                            <span v-else-if="pay.status === 'partial'" class="text-[10px] font-bold text-blue-700">Partial</span>
                                            <span v-else class="text-[10px] font-bold text-red-500 uppercase">Overdue</span>
                                        </td>
                                        <td class="p-2 text-right font-mono text-[10px]">{{ pay.receipt_number || '---' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else class="h-64 border border-dashed border-neutral-200 dark:border-neutral-800 rounded-xl flex items-center justify-center text-neutral-400">
                        Please search for a student's ID above to view tuition statement.
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-neutral-950 py-8 px-6 text-center text-neutral-500 text-xs border-t border-neutral-900 mt-12">
            <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>&copy; 2026 Antigravity Model School. All Rights Reserved.</div>
                <div class="flex gap-4">
                    <a href="#" class="hover:underline">Privacy Policy</a>
                    <a href="#" class="hover:underline">Terms of Service</a>
                    <a href="#" class="hover:underline">Admissions Policy</a>
                </div>
            </div>
        </footer>
    </div>
</template>
