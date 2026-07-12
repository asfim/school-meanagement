<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    classes: string[];
}>();

const form = useForm({
    class: 'Class 6',
    month: new Date().toISOString().slice(0, 7), // current month format "YYYY-MM"
    amount: 1500,
});

function submit() {
    form.post('/fees/billing', {
        onSuccess: () => {
            alert('Monthly tuition billings generated successfully.');
        }
    });
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tuition Fees', href: '/fees' },
    { title: 'Generate Billing Schedule', href: '#' },
];
</script>

<template>
    <Head title="Generate Monthly Billings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-xl mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-neutral-900 dark:text-neutral-50">Generate Billings</h1>
                    <p class="text-sm text-neutral-500">Bulk generate student invoices for tuition fees by class.</p>
                </div>
                <Link href="/fees" class="text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:underline">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm space-y-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Target Class *</label>
                    <select v-model="form.class" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                        <option v-for="c in classes" :key="c" :value="c">{{ c }}</option>
                    </select>
                    <span v-if="form.errors.class" class="text-xs text-red-500">{{ form.errors.class }}</span>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Billing Month *</label>
                    <input
                        v-model="form.month"
                        type="month"
                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                    />
                    <span v-if="form.errors.month" class="text-xs text-red-500">{{ form.errors.month }}</span>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Billing Amount ($) *</label>
                    <input
                        v-model.number="form.amount"
                        type="number"
                        min="0"
                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                    />
                    <span v-if="form.errors.amount" class="text-xs text-red-500">{{ form.errors.amount }}</span>
                    <p class="text-xs text-neutral-400 mt-1">Suggested defaults: Class 6-8: $1500, Class 9-10: $2000</p>
                </div>

                <div class="border-t border-neutral-100 dark:border-neutral-800 pt-4 flex justify-end gap-3">
                    <Link href="/fees" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 text-sm font-semibold rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 hover:bg-neutral-800 text-sm font-semibold rounded-lg text-white shadow">
                        Generate Bills
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
