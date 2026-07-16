<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

interface StudentRow {
    id: number;
    student_id: string;
    full_name_en: string;
    roll_number: number;
    section: string;
    program_name: string;
    current_semester: string;
    semester_id: number | null;
    exam_results: Array<{
        semester_id: number;
        semester_exam_id: number;
        marks: Record<string, number | string>;
        pass_status: string;
        remarks: string | null;
    }>;
}

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
    students: StudentRow[];
    subjects: string[];
    semesters: Semester[];
    programs: string[];
    sections: string[];
    program_name: string;
    section: string;
    currentFilters: {
        program_name: string;
        section: string;
        search?: string;
    };
}>();

const selectedProgram = ref(props.program_name);
const selectedSection = ref(props.section);
const search = ref(props.currentFilters.search || '');

const showAddMarksModal = ref(false);
const activeStudent = ref<StudentRow | null>(null);

const form = useForm({
    student_id: null as number | null,
    semester_id: '' as string | number,
    semester_exam_id: '' as string | number,
    marks: {} as Record<string, number | string>,
    remarks: '',
});

// Computed list of exams for the selected semester
const selectedSemesterExams = computed(() => {
    if (!form.semester_id) return [];
    const semester = props.semesters.find(s => s.id === Number(form.semester_id));
    return semester ? semester.exams : [];
});

function canAddMarks(student: StudentRow): boolean {
    const results = student.exam_results || [];
    const currentSemesterResults = results.filter(res => res.semester_id === student.semester_id);

    if (currentSemesterResults.length === 0) {
        return true;
    }

    // Check if there is any failed subject (score < 33) in the current semester
    const hasFail = currentSemesterResults.some(res => {
        return Object.values(res.marks).some(score => score !== '' && Number(score) < 33);
    });

    if (hasFail) {
        return true;
    }

    // If they haven't entered marks for all exams of the current semester, they can still add marks
    const semester = props.semesters.find(s => s.id === student.semester_id);
    const totalExams = semester ? semester.exams.length : 0;
    if (currentSemesterResults.length < totalExams) {
        return true;
    }

    return false;
}

function isSubjectDisabled(sub: string): boolean {
    if (!activeStudent.value || !form.semester_id || !form.semester_exam_id) {
        return false;
    }
    const results = activeStudent.value.exam_results || [];
    // Find result matching the selected semester and exam
    const examResult = results.find(res =>
        res.semester_id === Number(form.semester_id) &&
        res.semester_exam_id === Number(form.semester_exam_id)
    );
    if (!examResult) {
        return false;
    }
    const score = examResult.marks[sub];
    return score !== undefined && score !== null && score !== '' && Number(score) >= 60;
}

function getSubjectStatus(sub: string) {
    if (!activeStudent.value || !form.semester_id || !form.semester_exam_id) {
        return null;
    }
    const results = activeStudent.value.exam_results || [];
    const examResult = results.find(res =>
        res.semester_id === Number(form.semester_id) &&
        res.semester_exam_id === Number(form.semester_exam_id)
    );
    if (!examResult) {
        return null;
    }
    const score = examResult.marks[sub];
    if (score === undefined || score === null || score === '') {
        return null;
    }

    const numScore = Number(score);
    if (numScore < 33) {
        return { text: 'Failed', class: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/20 dark:text-red-400 dark:border-red-800' };
    } else if (numScore < 60) {
        return { text: 'Improvement (<60)', class: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-800' };
    } else {
        return { text: 'Passed (Locked)', class: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950/20 dark:text-green-400 dark:border-green-800' };
    }
}

watch(() => form.semester_id, () => {
    form.semester_exam_id = '';
    const marksObj: Record<string, number | string> = {};
    props.subjects.forEach(sub => {
        marksObj[sub] = '';
    });
    form.marks = marksObj;
    form.remarks = '';
});

watch(() => form.semester_exam_id, (newExamId) => {
    const marksObj: Record<string, number | string> = {};
    props.subjects.forEach(sub => {
        marksObj[sub] = '';
    });

    if (activeStudent.value && newExamId && form.semester_id) {
        const results = activeStudent.value.exam_results || [];
        const examResult = results.find(res =>
            res.semester_id === Number(form.semester_id) &&
            res.semester_exam_id === Number(newExamId)
        );
        if (examResult) {
            props.subjects.forEach(sub => {
                marksObj[sub] = examResult.marks[sub] !== undefined ? examResult.marks[sub] : '';
            });
            form.remarks = examResult.remarks || '';
        } else {
            form.remarks = '';
        }
    } else {
        form.remarks = '';
    }
    form.marks = marksObj;
});

function applyFilters() {
    router.get('/results/marks-entry', {
        program_name: selectedProgram.value,
        section: selectedSection.value,
        search: search.value,
    });
}

function openAddMarks(student: StudentRow) {
    activeStudent.value = student;
    form.student_id = student.id;
    form.semester_id = student.semester_id || '';
    form.semester_exam_id = '';

    // Initialize marks fields
    const marksObj: Record<string, number | string> = {};
    props.subjects.forEach(sub => {
        marksObj[sub] = '';
    });
    form.marks = marksObj;
    form.remarks = '';

    showAddMarksModal.value = true;
}

function submitMarks() {
    form.post('/results/marks-entry', {
        onSuccess: () => {
            showAddMarksModal.value = false;
            form.reset();
            alert('Academic marks recorded successfully.');
        }
    });
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Academic Results', href: '/results' },
    { title: 'Marks Entry Directory', href: '#' },
];
</script>

<template>
    <Head title="Add Marks Directory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-neutral-900 dark:text-neutral-50">Add Student Marks</h1>
                    <p class="text-sm text-neutral-500">Select a student from the listing below to record or update examination scores.</p>
                </div>
                <Link href="/results" class="text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:underline">&larr; Back to Results</Link>
            </div>

            <!-- Filters Toolbar -->
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-4 shadow-sm grid grid-cols-1 sm:grid-cols-4 gap-4 animate-in fade-in duration-200">
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 mb-1">Search Student</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by Roll, Name, ID..."
                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-450 focus:outline-none"
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
                <div class="flex items-end">
                    <button
                        @click="applyFilters"
                        class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow transition"
                    >
                        Filter Students
                    </button>
                </div>
            </div>

            <!-- Student Directory Table -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-950 text-neutral-500 dark:text-neutral-400 font-semibold border-b border-neutral-200 dark:border-neutral-800">
                                <th class="p-4">Roll</th>
                                <th class="p-4">Student ID</th>
                                <th class="p-4">Student Name</th>
                                <th class="p-4 text-center">Section</th>
                                <th class="p-4 text-center">Current Semester</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="student in students" :key="student.id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-950/30 text-neutral-700 dark:text-neutral-300">
                                <td class="p-4 font-bold text-neutral-950 dark:text-neutral-100">{{ student.roll_number }}</td>
                                <td class="p-4 font-mono text-xs font-semibold">{{ student.student_id }}</td>
                                <td class="p-4 font-semibold text-neutral-950 dark:text-neutral-100">{{ student.full_name_en }}</td>
                                <td class="p-4 text-center">{{ student.section }}</td>
                                <td class="p-4 text-center font-medium">{{ student.current_semester }}</td>
                                <td class="p-4 text-right">
                                    <button
                                        v-if="canAddMarks(student)"
                                        @click="openAddMarks(student)"
                                        class="inline-flex items-center justify-center rounded-lg bg-neutral-950 hover:bg-neutral-800 text-white px-3 py-1.5 text-xs font-bold transition dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200"
                                    >
                                        Add Marks
                                    </button>
                                    <span v-else class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-450 select-none">
                                        Completed
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="students.length === 0">
                                <td colspan="6" class="p-8 text-center text-neutral-500">No students found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Marks Modal Dialog -->
        <div v-if="showAddMarksModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-w-lg w-full rounded-xl p-6 space-y-4 shadow-xl">
                <div>
                    <h3 class="text-lg font-black text-neutral-900 dark:text-neutral-50">Enter Student Scores</h3>
                    <p class="text-xs text-neutral-400">Student: {{ activeStudent?.full_name_en }} ({{ activeStudent?.student_id }})</p>
                </div>

                <form @submit.prevent="submitMarks" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Semester *</label>
                            <select v-model="form.semester_id" required class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm focus:outline-none">
                                <option value="">Select Semester</option>
                                <option v-for="s in semesters" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Exam Term *</label>
                            <select v-model="form.semester_exam_id" required :disabled="!form.semester_id" class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm focus:outline-none">
                                <option value="">Select Exam</option>
                                <option v-for="e in selectedSemesterExams" :key="e.id" :value="e.id">{{ e.name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Subjects Marks Fields (only show if semester and exam both selected) -->
                    <div v-if="form.semester_id && form.semester_exam_id" class="space-y-3 pt-2">
                        <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Subject Scores</h4>
                        <div class="grid grid-cols-2 gap-4 max-h-60 overflow-y-auto pr-1">
                            <div v-for="sub in subjects" :key="sub" class="flex items-center justify-between border border-neutral-100 dark:border-neutral-800 p-2 rounded-lg bg-neutral-50 dark:bg-neutral-950">
                                <div class="flex flex-col">
                                    <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">{{ sub }}</span>
                                    <span v-if="getSubjectStatus(sub)" :class="['inline-self-start text-[8px] font-bold px-1 py-0.5 rounded border mt-0.5 w-max', getSubjectStatus(sub).class]">
                                        {{ getSubjectStatus(sub).text }}
                                    </span>
                                </div>
                                <input
                                    v-model.number="form.marks[sub]"
                                    type="number"
                                    min="0"
                                    max="100"
                                    placeholder="0-100"
                                    :disabled="isSubjectDisabled(sub)"
                                    class="w-16 text-center rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-2 py-1 text-xs font-bold disabled:bg-neutral-100 dark:disabled:bg-neutral-800 disabled:text-neutral-450 disabled:cursor-not-allowed"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold mb-1">Remarks</label>
                            <input
                                v-model="form.remarks"
                                type="text"
                                placeholder="Feedback or comments..."
                                class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm"
                            />
                        </div>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3">
                        <button type="button" @click="showAddMarksModal = false" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-sm text-neutral-700 dark:text-neutral-300">Cancel</button>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.semester_id || !form.semester_exam_id"
                            class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 text-white hover:bg-neutral-800 rounded-lg text-sm font-semibold"
                        >
                            Save Scores
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
