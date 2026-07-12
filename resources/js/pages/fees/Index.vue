<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface FeeSheetRow {
    student_id: number;
    student_uid: string;
    full_name: string;
    roll_number: number;
    has_billing: boolean;
    payment_id: number | null;
    amount_due: number;
    amount_paid: number;
    discount: number;
    status: 'paid' | 'partial' | 'unpaid' | 'unbilled';
    receipt_number: string | null;
    payment_date: string | null;
}

const props = defineProps<{
    feeSheet: FeeSheetRow[];
    classes: string[];
    sections: string[];
    currentFilters: {
        class: string;
        section: string;
        month: string;
    };
    summary: {
        total_collected: number;
        total_dues: number;
        total_discounts: number;
    };
}>();

const selectedClass = ref(props.currentFilters.class);
const selectedSection = ref(props.currentFilters.section);
const selectedMonth = ref(props.currentFilters.month);

const showCollectModal = ref(false);
const activeRow = ref<FeeSheetRow | null>(null);

const collectForm = useForm({
    payment_id: null as number | null,
    amount_paid: 0,
    discount: 0,
    payment_method: 'cash',
    remarks: '',
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tuition Fees', href: '/fees' },
];

function applyFilters() {
    router.get('/fees', {
        class: selectedClass.value,
        section: selectedSection.value,
        month: selectedMonth.value,
    }, {
        preserveState: true,
    });
}

// Watch filters
watch([selectedClass, selectedSection, selectedMonth], () => {
    applyFilters();
});

function openCollectModal(row: FeeSheetRow) {
    activeRow.value = row;
    collectForm.payment_id = row.payment_id;
    // suggest paying remaining due amount
    const remaining = row.amount_due - row.amount_paid - row.discount;
    collectForm.amount_paid = remaining > 0 ? remaining : 0;
    collectForm.discount = 0;
    collectForm.payment_method = 'cash';
    collectForm.remarks = '';
    showCollectModal.value = true;
}

function submitCollection() {
    collectForm.post('/fees/collect', {
        onSuccess: () => {
            showCollectModal.value = false;
            collectForm.reset();
        }
    });
}
</script>

<template>
    <Head title="Tuition Fee Directory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">Tuition Fee Registry</h1>
                    <p class="mt-1 text-sm text-neutral-500">Generate monthly student fee schedules, log fee payments, and issue official print receipts.</p>
                </div>
                <div class="flex gap-2">
                    <Link
                        href="/fees/billing"
                        class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200 shadow transition"
                    >
                        Generate Monthly Bills
                    </Link>
                </div>
            </div>

            <!-- Financial Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-5 shadow-sm">
                    <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">Total Collections</span>
                    <span class="text-3xl font-black text-green-600 dark:text-green-400 mt-2 block font-mono">${{ summary.total_collected.toFixed(2) }}</span>
                </div>
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-5 shadow-sm">
                    <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">Outstanding Dues</span>
                    <span class="text-3xl font-black text-red-500 dark:text-red-400 mt-2 block font-mono">${{ summary.total_dues.toFixed(2) }}</span>
                </div>
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-5 shadow-sm">
                    <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">Discounts Given</span>
                    <span class="text-3xl font-black text-amber-500 dark:text-amber-400 mt-2 block font-mono">${{ summary.total_discounts.toFixed(2) }}</span>
                </div>
            </div>

            <!-- Filters Toolbar -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 mb-1">Billing Month *</label>
                    <input
                        v-model="selectedMonth"
                        type="month"
                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 mb-1">Select Class *</label>
                    <select v-model="selectedClass" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                        <option v-for="c in classes" :key="c" :value="c">{{ c }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 mb-1">Select Section *</label>
                    <select v-model="selectedSection" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                        <option v-for="s in sections" :key="s" :value="s">Section {{ s }}</option>
                    </select>
                </div>
            </div>

            <!-- Fee Registry Table -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-950 text-neutral-500 dark:text-neutral-400 font-semibold border-b border-neutral-200 dark:border-neutral-800">
                                <th class="p-4">Roll</th>
                                <th class="p-4">Student ID</th>
                                <th class="p-4">Full Name</th>
                                <th class="p-4 text-center">Due Amt</th>
                                <th class="p-4 text-center">Paid Amt</th>
                                <th class="p-4 text-center">Discount</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="row in feeSheet" :key="row.student_id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-950/30 text-neutral-700 dark:text-neutral-300">
                                <td class="p-4 font-bold text-neutral-950 dark:text-neutral-100">#{{ row.roll_number }}</td>
                                <td class="p-4 font-mono text-xs font-semibold">{{ row.student_uid }}</td>
                                <td class="p-4 font-semibold text-neutral-950 dark:text-neutral-100">{{ row.full_name }}</td>
                                <td class="p-4 text-center font-mono font-bold">${{ row.amount_due }}</td>
                                <td class="p-4 text-center font-mono font-medium text-green-600 dark:text-green-450">${{ row.amount_paid }}</td>
                                <td class="p-4 text-center font-mono text-amber-500">${{ row.discount }}</td>
                                <td class="p-4 text-center">
                                    <span v-if="row.status === 'paid'" class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-green-50 text-green-700 border border-green-200">Paid</span>
                                    <span v-else-if="row.status === 'partial'" class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Partial</span>
                                    <span v-else-if="row.status === 'unpaid'" class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-red-50 text-red-700 border border-red-200 animate-pulse">Overdue</span>
                                    <span v-else class="text-xs text-neutral-400 italic">unbilled</span>
                                </td>
                                <td class="p-4 text-right space-x-2">
                                    <button
                                        v-if="row.has_billing && row.status !== 'paid'"
                                        @click="openCollectModal(row)"
                                        class="px-2 py-1 bg-neutral-950 hover:bg-neutral-800 text-white rounded text-xs font-bold"
                                    >
                                        Collect
                                    </button>
                                    <Link
                                        v-if="row.has_billing && row.receipt_number"
                                        :href="'/fees/' + row.payment_id + '/receipt'"
                                        class="px-2 py-1 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-xs font-bold rounded text-neutral-800 dark:text-neutral-200"
                                    >
                                        Receipt
                                    </Link>
                                    <span v-if="!row.has_billing" class="text-xs text-neutral-400">unbilled</span>
                                </td>
                            </tr>
                            <tr v-if="feeSheet.length === 0">
                                <td colspan="8" class="p-8 text-center text-neutral-500">No students enrolled.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Collection modal dialog -->
        <div v-if="showCollectModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-w-md w-full rounded-xl p-6 space-y-4 shadow-xl">
                <div>
                    <h3 class="text-lg font-black text-neutral-900 dark:text-neutral-50">Record Fee Collection</h3>
                    <p class="text-xs text-neutral-400">Student: {{ activeRow?.full_name }} ({{ activeRow?.student_uid }})</p>
                </div>
                <form @submit.prevent="submitCollection" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Amount to Pay ($) *</label>
                        <input
                            v-model.number="collectForm.amount_paid"
                            type="number"
                            min="0"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Apply Scholarship / Discount ($)</label>
                        <input
                            v-model.number="collectForm.discount"
                            type="number"
                            min="0"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Payment Method *</label>
                        <select v-model="collectForm.payment_method" class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm focus:outline-none">
                            <option value="cash">Cash Collection</option>
                            <option value="bank">Bank Wire</option>
                            <option value="mobile_banking">Mobile Banking / Wallet</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Collection Remarks</label>
                        <input
                            v-model="collectForm.remarks"
                            type="text"
                            placeholder="Optional notes..."
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm"
                        />
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3">
                        <button type="button" @click="showCollectModal = false" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-sm text-neutral-700 dark:text-neutral-300">Cancel</button>
                        <button type="submit" :disabled="collectForm.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 text-white hover:bg-neutral-800 rounded-lg text-sm font-semibold">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
