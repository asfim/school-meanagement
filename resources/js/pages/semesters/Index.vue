<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface SemesterExam {
    id: number;
    semester_id: number;
    name: string;
    is_final: boolean;
    sort_order: number;
}

interface Semester {
    id: number;
    name: string;
    sort_order: number;
    exams: SemesterExam[];
}

const props = defineProps<{
    semesters: Semester[];
}>();

const editingSemester = ref<Semester | null>(null);
const showAddSemesterModal = ref(false);
const showAddExamModal = ref(false);
const activeSemesterForExam = ref<Semester | null>(null);
const editingExam = ref<SemesterExam | null>(null);

const semesterForm = useForm({
    name: '',
    sort_order: 1,
});

const examForm = useForm({
    name: '',
    sort_order: 1,
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Academic Semesters', href: '#' },
];

function openAddSemester() {
    editingSemester.value = null;
    semesterForm.reset();
    semesterForm.name = '';
    semesterForm.sort_order = props.semesters.length + 1;
    showAddSemesterModal.value = true;
}

function openEditSemester(semester: Semester) {
    editingSemester.value = semester;
    semesterForm.name = semester.name;
    semesterForm.sort_order = semester.sort_order;
    showAddSemesterModal.value = true;
}

function submitSemester() {
    if (editingSemester.value) {
        semesterForm.put(`/semesters/${editingSemester.value.id}`, {
            onSuccess: () => {
                showAddSemesterModal.value = false;
                semesterForm.reset();
            }
        });
    } else {
        semesterForm.post('/semesters', {
            onSuccess: () => {
                showAddSemesterModal.value = false;
                semesterForm.reset();
            }
        });
    }
}

function deleteSemester(id: number) {
    if (confirm('Are you sure you want to delete this semester? This will delete all student records and marks associated with this semester!')) {
        semesterForm.delete(`/semesters/${id}`);
    }
}

function openAddExam(semester: Semester) {
    activeSemesterForExam.value = semester;
    editingExam.value = null;
    examForm.reset();
    examForm.name = '';
    examForm.sort_order = semester.exams.length + 1;
    showAddExamModal.value = true;
}

function openEditExam(semester: Semester, exam: SemesterExam) {
    activeSemesterForExam.value = semester;
    editingExam.value = exam;
    examForm.name = exam.name;
    examForm.sort_order = exam.sort_order;
    showAddExamModal.value = true;
}

function submitExam() {
    if (editingExam.value) {
        examForm.put(`/semester-exams/${editingExam.value.id}`, {
            onSuccess: () => {
                showAddExamModal.value = false;
                examForm.reset();
            }
        });
    } else if (activeSemesterForExam.value) {
        examForm.post(`/semesters/${activeSemesterForExam.value.id}/exams`, {
            onSuccess: () => {
                showAddExamModal.value = false;
                examForm.reset();
            }
        });
    }
}

function deleteExam(id: number) {
    if (confirm('Are you sure you want to delete this exam term?')) {
        examForm.delete(`/semester-exams/${id}`);
    }
}
</script>

<template>
    <Head title="Manage Semesters & Exams" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">Academic Semesters</h1>
                    <p class="mt-1 text-sm text-neutral-500">Configure semesters, dynamic terms, and static final exams used in student records.</p>
                </div>
                <div>
                    <button
                        @click="openAddSemester"
                        class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200 shadow transition"
                    >
                        Create Semester
                    </button>
                </div>
            </div>

            <!-- Semesters Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div v-for="sem in semesters" :key="sem.id" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-5 shadow-sm space-y-4 animate-in fade-in duration-200">
                    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-3">
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-50">{{ sem.name }}</h3>
                            <span class="text-xs text-neutral-400">Order: {{ sem.sort_order }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button @click="openEditSemester(sem)" class="text-xs font-semibold text-blue-600 hover:underline">Edit</button>
                            <button @click="deleteSemester(sem.id)" class="text-xs font-semibold text-red-650 hover:underline">Delete</button>
                        </div>
                    </div>

                    <!-- Exams List -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Exam Terms</h4>
                            <button @click="openAddExam(sem)" class="text-xs text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 font-bold">+ Add Term</button>
                        </div>

                        <div class="overflow-hidden border border-neutral-100 dark:border-neutral-800 rounded-lg">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-neutral-50 dark:bg-neutral-950 text-neutral-500 font-semibold border-b border-neutral-100 dark:border-neutral-800">
                                        <th class="p-2.5">Exam Name</th>
                                        <th class="p-2.5 text-center">Type</th>
                                        <th class="p-2.5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800 text-neutral-700 dark:text-neutral-350">
                                    <tr v-for="exam in sem.exams" :key="exam.id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-950/20">
                                        <td class="p-2.5 font-medium">{{ exam.name }}</td>
                                        <td class="p-2.5 text-center">
                                            <span v-if="exam.is_final" class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Final (Static)</span>
                                            <span v-else class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-neutral-150 text-neutral-700 border border-neutral-250">Term</span>
                                        </td>
                                        <td class="p-2.5 text-right space-x-2">
                                            <template v-if="!exam.is_final">
                                                <button @click="openEditExam(sem, exam)" class="text-blue-600 hover:underline">Edit</button>
                                                <button @click="deleteExam(exam.id)" class="text-red-500 hover:underline">Delete</button>
                                            </template>
                                            <span v-else class="text-neutral-400 italic">locked</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div v-if="semesters.length === 0" class="col-span-full bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-800 p-12 rounded-xl text-center text-neutral-500">
                    No academic semesters set up. Click "Create Semester" to configure your first semester.
                </div>
            </div>
        </div>

        <!-- Add Semester Modal -->
        <div v-if="showAddSemesterModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-w-md w-full rounded-xl p-6 space-y-4 shadow-xl">
                <div>
                    <h3 class="text-lg font-black text-neutral-900 dark:text-neutral-50">{{ editingSemester ? 'Edit Semester' : 'Create Semester' }}</h3>
                    <p class="text-xs text-neutral-400">Specify semester name and sequencing order.</p>
                </div>
                <form @submit.prevent="submitSemester" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Semester Name *</label>
                        <input
                            v-model="semesterForm.name"
                            type="text"
                            placeholder="e.g. 1st Semester"
                            required
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none"
                        />
                        <span v-if="semesterForm.errors.name" class="text-xs text-red-500">{{ semesterForm.errors.name }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Sort Order *</label>
                        <input
                            v-model.number="semesterForm.sort_order"
                            type="number"
                            min="1"
                            required
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                    </div>
                    <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3">
                        <button type="button" @click="showAddSemesterModal = false" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-sm text-neutral-700 dark:text-neutral-300">Cancel</button>
                        <button type="submit" :disabled="semesterForm.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 text-white hover:bg-neutral-800 rounded-lg text-sm font-semibold">Save Semester</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Exam Modal -->
        <div v-if="showAddExamModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-w-md w-full rounded-xl p-6 space-y-4 shadow-xl">
                <div>
                    <h3 class="text-lg font-black text-neutral-900 dark:text-neutral-50">{{ editingExam ? 'Edit Exam Term' : 'Add Exam Term' }}</h3>
                    <p class="text-xs text-neutral-400">Configure exam terms for {{ activeSemesterForExam?.name }}.</p>
                </div>
                <form @submit.prevent="submitExam" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Exam Term Name *</label>
                        <input
                            v-model="examForm.name"
                            type="text"
                            placeholder="e.g. 1st Term"
                            required
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none"
                        />
                        <span v-if="examForm.errors.name" class="text-xs text-red-500">{{ examForm.errors.name }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Sort Order *</label>
                        <input
                            v-model.number="examForm.sort_order"
                            type="number"
                            min="1"
                            required
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                    </div>
                    <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3">
                        <button type="button" @click="showAddExamModal = false" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-sm text-neutral-700 dark:text-neutral-300">Cancel</button>
                        <button type="submit" :disabled="examForm.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 text-white hover:bg-neutral-800 rounded-lg text-sm font-semibold">Save Exam</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
