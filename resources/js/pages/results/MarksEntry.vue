<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface StudentMarksRow {
    student_id: number;
    student_uid: string;
    full_name: string;
    roll_number: number;
    marks: Record<string, number | string>;
    remarks: string;
}

const props = defineProps<{
    marksSheet: StudentMarksRow[];
    subjects: string[];
    class: string;
    section: string;
    exam_name: string;
}>();

const form = useForm({
    class: props.class,
    section: props.section,
    exam_name: props.exam_name,
    results: props.marksSheet.map(row => ({
        student_id: row.student_id,
        marks: { ...row.marks },
        remarks: row.remarks || '',
    })),
});

function submit() {
    form.post('/results/marks-entry', {
        onSuccess: () => {
            alert('Academic marks recorded successfully.');
        }
    });
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Academic Results', href: '/results' },
    { title: 'Marks Entry Sheet', href: '#' },
];
</script>

<template>
    <Head title="Bulk Marks Entry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-neutral-900 dark:text-neutral-50">Bulk Marks Entry Sheet</h1>
                    <p class="text-sm text-neutral-500">Record scores for {{ props.class }} (Sec {{ section }}) &mdash; {{ exam_name }}</p>
                </div>
                <Link :href="'/results?class=' + props.class + '&section=' + section + '&exam_name=' + exam_name" class="text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:underline">&larr; Back</Link>
            </div>

            <!-- Entry Form Sheet -->
            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm space-y-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-950 border-b border-neutral-200 dark:border-neutral-800">
                                <th class="p-4 font-semibold text-neutral-500 w-16">Roll</th>
                                <th class="p-4 font-semibold text-neutral-500">Student Info</th>
                                <th v-for="sub in subjects" :key="sub" class="p-4 font-semibold text-neutral-500 w-28 text-center">{{ sub }}</th>
                                <th class="p-4 font-semibold text-neutral-500">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="(student, sIndex) in marksSheet" :key="student.student_id" class="text-neutral-700 dark:text-neutral-300">
                                <td class="p-4 font-bold text-neutral-950 dark:text-neutral-100">{{ student.roll_number }}</td>
                                <td class="p-4">
                                    <div class="font-bold text-neutral-950 dark:text-neutral-100">{{ student.full_name }}</div>
                                    <div class="text-[10px] font-mono text-neutral-450">{{ student.student_uid }}</div>
                                </td>
                                <!-- Subjects Loop -->
                                <td v-for="sub in subjects" :key="sub" class="p-4 text-center">
                                    <input
                                        v-model="form.results[sIndex].marks[sub]"
                                        type="number"
                                        min="0"
                                        max="100"
                                        placeholder="0-100"
                                        class="w-20 text-center rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-2 py-1.5 font-bold focus:outline-none"
                                    />
                                </td>
                                <td class="p-4">
                                    <input
                                        v-model="form.results[sIndex].remarks"
                                        type="text"
                                        placeholder="Feedback comments..."
                                        class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-1.5 text-xs text-neutral-950 dark:text-neutral-100 focus:outline-none"
                                    />
                                </td>
                            </tr>
                            <tr v-if="marksSheet.length === 0">
                                <td :colspan="3 + subjects.length" class="p-8 text-center text-neutral-500">No students found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-neutral-100 dark:border-neutral-800 pt-4 flex justify-end gap-3">
                    <Link :href="'/results?class=' + props.class + '&section=' + section + '&exam_name=' + exam_name" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 text-sm font-semibold rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 hover:bg-neutral-800 text-sm font-semibold rounded-lg text-white shadow">
                        Save Marks Sheet
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
