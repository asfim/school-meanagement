<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface Student {
    id: number;
    student_id: string;
    full_name_en: string;
    full_name_native: string;
    program_name: string;
    section: string;
    roll_number: number;
}

interface FeePayment {
    id: number;
    fee_month: string;
    amount_due: string;
    amount_paid: string;
    discount: string;
    payment_date: string | null;
    payment_method: string | null;
    receipt_number: string | null;
    status: 'paid' | 'partial' | 'unpaid';
    remarks: string | null;
    student: Student;
}

const props = defineProps<{
    payment: FeePayment;
}>();

function printReceipt() {
    window.print();
}
</script>

<template>
    <Head :title="`Receipt - ${payment.receipt_number || 'Billing Slip'}`" />

    <div class="min-h-screen bg-neutral-100 dark:bg-neutral-950 p-6 flex flex-col items-center print:bg-white print:p-0">
        <!-- Control buttons (hidden on print) -->
        <div class="mb-6 w-full max-w-lg flex justify-between items-center print:hidden">
            <Link
                :href="'/fees?program_name=' + payment.student.program_name + '&section=' + payment.student.section + '&month=' + payment.fee_month"
                class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 hover:bg-neutral-50 rounded-lg text-sm font-semibold text-neutral-700 dark:text-neutral-300"
            >
                &larr; Back to Registry
            </Link>
            <button
                @click="printReceipt"
                class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 hover:bg-neutral-800 text-white rounded-lg text-sm font-semibold shadow"
            >
                Print Receipt
            </button>
        </div>

        <!-- Official Payment Slip Card -->
        <div class="w-full max-w-lg bg-white border border-neutral-200 p-8 rounded-xl shadow-sm text-neutral-900 font-sans print:shadow-none print:border-none print:p-0">
            <!-- Header section -->
            <div class="text-center border-b border-neutral-200 pb-4">
                <h1 class="text-xl font-black uppercase tracking-wider">Antigravity Model School</h1>
                <p class="text-[10px] uppercase tracking-widest text-neutral-500 mt-1">Official Tuition Payment Slip</p>
            </div>

            <!-- Receipt Metadata -->
            <div class="my-6 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-neutral-500">Receipt Number:</span>
                    <span class="font-bold font-mono">{{ payment.receipt_number || 'PENDING' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-500">Date Logged:</span>
                    <span class="font-semibold">{{ payment.payment_date ? new Date(payment.payment_date).toLocaleDateString() : 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-500">Billing Month:</span>
                    <span class="font-semibold">{{ payment.fee_month }}</span>
                </div>
            </div>

            <div class="border-t border-neutral-200 my-4"></div>

            <!-- Student Bio Details -->
            <div class="space-y-1.5 text-sm">
                <h3 class="font-bold text-xs uppercase text-neutral-400 tracking-wider">Student Details</h3>
                <div class="grid grid-cols-2 gap-2 mt-1">
                    <div><span class="text-neutral-500">Student Name:</span> <span class="font-bold">{{ payment.student.full_name_en }}</span></div>
                    <div><span class="text-neutral-500">Student ID:</span> <span class="font-bold font-mono">{{ payment.student.student_id }}</span></div>
                    <div><span class="text-neutral-500">Program:</span> <span class="font-semibold">{{ payment.student.program_name }} (Sec {{ payment.student.section }})</span></div>
                    <div><span class="text-neutral-500">Roll Number:</span> <span class="font-semibold">{{ payment.student.roll_number }}</span></div>
                </div>
            </div>

            <div class="border-t border-neutral-200 my-4"></div>

            <!-- Ledger Sheet -->
            <div class="space-y-3">
                <h3 class="font-bold text-xs uppercase text-neutral-400 tracking-wider">Financial Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Standard Monthly Fee:</span>
                        <span class="font-semibold">${{ parseFloat(payment.amount_due).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between text-amber-600">
                        <span>Scholarship / Discount Applied:</span>
                        <span>-${{ parseFloat(payment.discount).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between text-neutral-500">
                        <span>Total Due Balance:</span>
                        <span>${{ (parseFloat(payment.amount_due) - parseFloat(payment.discount)).toFixed(2) }}</span>
                    </div>
                    <div class="border-t border-dashed border-neutral-200 my-2"></div>
                    <div class="flex justify-between text-green-600 font-bold text-base">
                        <span>Amount Paid:</span>
                        <span>${{ parseFloat(payment.amount_paid).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between text-red-500 font-bold">
                        <span>Remaining Balance:</span>
                        <span>${{ (parseFloat(payment.amount_due) - parseFloat(payment.discount) - parseFloat(payment.amount_paid)).toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-neutral-200 my-4"></div>

            <!-- Payment Details -->
            <div class="text-sm space-y-1.5">
                <div><span class="text-neutral-500">Payment Status:</span> 
                    <span class="ml-2 inline-flex px-2 py-0.5 rounded text-xs font-bold" :class="payment.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                        {{ payment.status.toUpperCase() }}
                    </span>
                </div>
                <div><span class="text-neutral-500">Payment Method:</span> <span class="font-semibold capitalize ml-2">{{ payment.payment_method || 'N/A' }}</span></div>
                <div><span class="text-neutral-500">Collection Notes:</span> <span class="italic ml-2">{{ payment.remarks || 'None.' }}</span></div>
            </div>

            <!-- Official Seal Footnote -->
            <div class="flex justify-between items-end mt-12 text-xs text-neutral-500">
                <div>
                    <p class="border-t border-neutral-300 pt-2 px-2 text-center w-28">Receiver Signature</p>
                </div>
                <div class="text-right">
                    <p class="border-t border-neutral-300 pt-2 px-2 text-center w-28">Accounts Seal</p>
                </div>
            </div>
        </div>
    </div>
</template>
