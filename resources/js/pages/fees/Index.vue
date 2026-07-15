<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

interface FeeTransaction {
    id: number;
    fee_payment_id: number;
    payment_date: string;
    tuition_fee: number;
    discount_type: 'none' | 'percentage' | 'fixed';
    discount_value: number;
    discount_amount: number;
    net_payable_amount: number;
    amount_paid: number;
    remaining_due: number;
    status_after_payment: string;
    receipt_number: string | null;
    remarks: string | null;
}

interface FeeSheetRow {
    student_id: number;
    student_uid: string;
    full_name: string;
    roll_number: number;
    tuition_fee: number;
    has_billing: boolean;
    payment_id: number | null;
    amount_due: number;
    amount_paid: number;
    discount: number;
    status: 'paid' | 'partial' | 'unpaid' | 'unbilled';
    receipt_number: string | null;
    payment_date: string | null;
    transactions: FeeTransaction[];
}

interface LinkItem {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedFeeSheet {
    data: FeeSheetRow[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: LinkItem[];
}

const props = defineProps<{
    feeSheet: PaginatedFeeSheet;
    programs: string[];
    sections: string[];
    currentFilters: {
        program_name: string;
        section: string;
        month: string;
        search?: string;
    };
    summary: {
        total_collected: number;
        total_dues: number;
        total_discounts: number;
    };
}>();

const selectedProgram = ref(props.currentFilters.program_name);
const selectedSection = ref(props.currentFilters.section);
const selectedMonth = ref(props.currentFilters.month);
const search = ref(props.currentFilters.search || '');

const showCollectModal = ref(false);
const showHistoryModal = ref(false);
const activeRow = ref<FeeSheetRow | null>(null);

const collectForm = useForm({
    payment_id: null as number | null,
    discount_type: 'none',
    discount_value: 0,
    discount_amount: 0,
    amount_paid: 0,
    payment_method: 'cash',
    remarks: '',
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tuition Fees', href: '/fees' },
];

function applyFilters() {
    router.get('/fees', {
        program_name: selectedProgram.value,
        section: selectedSection.value,
        month: selectedMonth.value,
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

// Watch filters
watch([selectedProgram, selectedSection, selectedMonth, search], () => {
    applyFilters();
});

const currentDue = computed(() => {
    if (!activeRow.value) return 0;
    return activeRow.value.amount_due - activeRow.value.amount_paid - activeRow.value.discount;
});

const calculatedDiscountAmount = computed(() => {
    if (collectForm.discount_type === 'percentage') {
        return (currentDue.value * (collectForm.discount_value || 0)) / 100;
    } else if (collectForm.discount_type === 'fixed') {
        return collectForm.discount_value || 0;
    }
    return 0;
});

const netPayableAmount = computed(() => {
    const net = currentDue.value - calculatedDiscountAmount.value;
    return net > 0 ? net : 0;
});

watch(netPayableAmount, (newVal) => {
    collectForm.amount_paid = Number(newVal.toFixed(2));
});

function openCollectModal(row: FeeSheetRow) {
    activeRow.value = row;
    collectForm.payment_id = row.payment_id;
    collectForm.discount_type = 'none';
    collectForm.discount_value = 0;
    collectForm.discount_amount = 0;
    
    const remaining = row.amount_due - row.amount_paid - row.discount;
    collectForm.amount_paid = remaining > 0 ? remaining : 0;
    collectForm.payment_method = 'cash';
    collectForm.remarks = '';
    showCollectModal.value = true;
}

function openHistoryModal(row: FeeSheetRow) {
    activeRow.value = row;
    showHistoryModal.value = true;
}

// Helper to wrap metric decimals inside Numbers to fixed 2
function safeToFixed(value: string | number | undefined): string {
    return Number(value || 0).toFixed(2);
}

function submitCollection() {
    collectForm.discount_amount = calculatedDiscountAmount.value;
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
                    <span class="text-3xl font-black text-green-600 dark:text-green-400 mt-2 block font-mono">${{ Number(summary.total_collected || 0).toFixed(2) }}</span>
                </div>
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-5 shadow-sm">
                    <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">Outstanding Dues</span>
                    <span class="text-3xl font-black text-red-500 dark:text-red-400 mt-2 block font-mono">${{ Number(summary.total_dues || 0).toFixed(2) }}</span>
                </div>
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-5 shadow-sm">
                    <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">Discounts Given</span>
                    <span class="text-3xl font-black text-amber-500 dark:text-amber-400 mt-2 block font-mono">${{ Number(summary.total_discounts || 0).toFixed(2) }}</span>
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
                    <label class="block text-xs font-semibold text-neutral-500 mb-1">Billing Month *</label>
                    <input
                        v-model="selectedMonth"
                        type="month"
                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                    />
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

            <!-- Fee Registry Table -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-950 text-neutral-500 dark:text-neutral-400 font-semibold border-b border-neutral-200 dark:border-neutral-800">
                                <th class="p-4">Roll</th>
                                <th class="p-4">Student ID</th>
                                <th class="p-4">Full Name</th>
                                <th class="p-4 text-center">Tuition Fee</th>
                                <th class="p-4 text-center">Due Amount</th>
                                <th class="p-4 text-center">Paid Amount</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="row in feeSheet.data" :key="row.student_id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-950/30 text-neutral-700 dark:text-neutral-300">
                                <td class="p-4 font-bold text-neutral-950 dark:text-neutral-100">{{ row.roll_number }}</td>
                                <td class="p-4 font-mono text-xs font-semibold">{{ row.student_uid }}</td>
                                <td class="p-4 font-semibold text-neutral-950 dark:text-neutral-100">{{ row.full_name }}</td>
                                <td class="p-4 text-center font-mono font-bold">${{ Number(row.amount_due).toFixed(2) }}</td>
                                <td class="p-4 text-center font-mono font-bold text-red-500">${{ Number(row.amount_due - row.amount_paid - row.discount).toFixed(2) }}</td>
                                <td class="p-4 text-center font-mono font-medium text-green-600 dark:text-green-450">${{ Number(row.amount_paid).toFixed(2) }}</td>
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
                                    <button
                                        v-if="row.has_billing"
                                        @click="openHistoryModal(row)"
                                        class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded text-xs font-bold"
                                    >
                                        View
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
                            <tr v-if="feeSheet.data.length === 0">
                                <td colspan="8" class="p-8 text-center text-neutral-500">No students enrolled matching filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="feeSheet.last_page > 1" class="p-4 bg-neutral-50 dark:bg-neutral-950 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <div class="text-xs text-neutral-500">Page {{ feeSheet.current_page }} of {{ feeSheet.last_page }}</div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in feeSheet.links"
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

        <!-- Collection modal dialog -->
        <div v-if="showCollectModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-w-md w-full rounded-xl p-6 space-y-4 shadow-xl">
                <div>
                    <h3 class="text-lg font-black text-neutral-900 dark:text-neutral-50">Record Fee Collection</h3>
                    <p class="text-xs text-neutral-400">Student: {{ activeRow?.full_name }} ({{ activeRow?.student_uid }})</p>
                </div>
                <form @submit.prevent="submitCollection" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Tuition Fee ($) (Current Due)</label>
                        <input
                            :value="safeToFixed(currentDue)"
                            type="text"
                            disabled
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-800 px-3 py-2 text-sm text-neutral-500 font-mono font-bold"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Discount Type *</label>
                        <select v-model="collectForm.discount_type" class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm focus:outline-none">
                            <option value="none">None</option>
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount ($)</option>
                        </select>
                    </div>
                    <div v-if="collectForm.discount_type !== 'none'">
                        <label class="block text-xs font-semibold mb-1">Apply Scholarship / Discount *</label>
                        <input
                            v-model.number="collectForm.discount_value"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 font-bold"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Net Payable Amount ($)</label>
                        <input
                            :value="safeToFixed(netPayableAmount)"
                            type="text"
                            disabled
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-800 px-3 py-2 text-sm text-neutral-500 font-mono font-bold"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Amount to Pay ($) *</label>
                        <input
                            v-model.number="collectForm.amount_paid"
                            type="number"
                            min="0.01"
                            :max="netPayableAmount"
                            step="0.01"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 font-bold"
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

        <!-- History modal dialog -->
        <div v-if="showHistoryModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-w-4xl w-full rounded-xl p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-3">
                    <div>
                        <h3 class="text-lg font-black text-neutral-900 dark:text-neutral-50">Tuition Payment History Ledger</h3>
                        <p class="text-xs text-neutral-400">Student: {{ activeRow?.full_name }} ({{ activeRow?.student_uid }}) &mdash; Billing Month: {{ props.currentFilters.month }}</p>
                    </div>
                    <button @click="showHistoryModal = false" class="text-neutral-400 hover:text-neutral-605 text-lg font-bold">&times;</button>
                </div>

                <div class="overflow-x-auto max-h-96">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-950 text-neutral-500 font-semibold border-b border-neutral-200 dark:border-neutral-800">
                                <th class="p-3">Payment Date</th>
                                <th class="p-3 text-center">Tuition Fee</th>
                                <th class="p-3 text-center">Discount Type</th>
                                <th class="p-3 text-center">Discount Value</th>
                                <th class="p-3 text-center">Discount Amount</th>
                                <th class="p-3 text-center">Net Payable</th>
                                <th class="p-3 text-center">Amount Paid</th>
                                <th class="p-3 text-center">Remaining Due</th>
                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="t in activeRow?.transactions" :key="t.id" class="text-neutral-700 dark:text-neutral-350">
                                <td class="p-3 font-mono font-medium">{{ new Date(t.payment_date).toLocaleDateString() }}</td>
                                <td class="p-3 text-center font-mono">${{ Number(t.tuition_fee).toFixed(2) }}</td>
                                <td class="p-3 text-center capitalize">{{ t.discount_type }}</td>
                                <td class="p-3 text-center font-mono">{{ t.discount_type === 'percentage' ? t.discount_value + '%' : (t.discount_type === 'none' ? '-' : '$' + Number(t.discount_value).toFixed(2)) }}</td>
                                <td class="p-3 text-center font-mono text-amber-500">${{ Number(t.discount_amount).toFixed(2) }}</td>
                                <td class="p-3 text-center font-mono font-bold">${{ Number(t.net_payable_amount).toFixed(2) }}</td>
                                <td class="p-3 text-center font-mono text-green-600 dark:text-green-455 font-bold">${{ Number(t.amount_paid).toFixed(2) }}</td>
                                <td class="p-3 text-center font-mono text-red-500">${{ Number(t.remaining_due).toFixed(2) }}</td>
                                <td class="p-3 text-center">
                                    <span v-if="t.status_after_payment === 'paid'" class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">Paid</span>
                                    <span v-else class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Partial</span>
                                </td>
                            </tr>
                            <tr v-if="!activeRow?.transactions || activeRow.transactions.length === 0">
                                <td colspan="9" class="p-8 text-center text-neutral-500">No payment transaction history found for this billing month.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end">
                    <button type="button" @click="showHistoryModal = false" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 text-white hover:bg-neutral-800 rounded-lg text-sm font-semibold">Close History</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
