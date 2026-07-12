<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Teacher {
    id: number;
    teacher_id: string;
    full_name: string;
    dob: string;
    mobile: string;
    email: string;
    qualifications: string;
    subjects: string[];
    classes: string[];
    date_of_joining: string;
    designation: string;
    salary_structure: {
        base_salary: number;
        allowances: number;
        deductions: number;
    };
    address: string;
    photo_path: string | null;
}

const props = defineProps<{
    teacher: Teacher | null;
    subjectsList: string[];
}>();

const isEdit = !!props.teacher;

const availableSubjects = props.subjectsList && props.subjectsList.length > 0
    ? props.subjectsList
    : ['Mathematics', 'English', 'Physics', 'Chemistry', 'Biology', 'History', 'Geography', 'Social Science'];
const availableClasses = ['Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10'];

const form = useForm({
    _method: isEdit ? 'PUT' : 'POST',
    full_name: props.teacher?.full_name || '',
    dob: props.teacher ? new Date(props.teacher.dob).toISOString().split('T')[0] : '',
    mobile: props.teacher?.mobile || '',
    email: props.teacher?.email || '',
    qualifications: props.teacher?.qualifications || '',
    subjects: props.teacher?.subjects || [] as string[],
    classes: props.teacher?.classes || [] as string[],
    date_of_joining: props.teacher ? new Date(props.teacher.date_of_joining).toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
    designation: props.teacher?.designation || '',
    salary_structure: {
        base_salary: props.teacher?.salary_structure?.base_salary || 0,
        allowances: props.teacher?.salary_structure?.allowances || 0,
        deductions: props.teacher?.salary_structure?.deductions || 0,
    },
    address: props.teacher?.address || '',
    photo: null as File | null,
});

const photoPreview = ref<string | null>(null);

function selectPhoto(e: Event) {
    const files = (e.target as HTMLInputElement).files;
    if (files && files[0]) {
        form.photo = files[0];
        const reader = new FileReader();
        reader.onload = (event) => {
            photoPreview.value = event.target?.result as string;
        };
        reader.readAsDataURL(files[0]);
    }
}

function submit() {
    if (isEdit) {
        form.post(`/teachers/${props.teacher?.id}`, {
            onSuccess: () => {},
        });
    } else {
        form.post('/teachers', {
            onSuccess: () => {},
        });
    }
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Teachers', href: '/teachers' },
    { title: isEdit ? 'Edit Teacher' : 'Add Teacher', href: '#' },
];
</script>

<template>
    <Head :title="isEdit ? 'Edit Faculty Details' : 'Add Faculty Member'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-4xl mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-neutral-900 dark:text-neutral-50">{{ isEdit ? 'Edit Faculty Profile' : 'Add Faculty Member' }}</h1>
                    <p class="text-sm text-neutral-500">Record biography details, qualifications, salary structure, and academic assignments.</p>
                </div>
                <Link href="/teachers" class="text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:underline">&larr; Back to Directory</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm space-y-6">
                <!-- Profile & Personal Details -->
                <div>
                    <h2 class="text-lg font-bold border-b border-neutral-100 dark:border-neutral-800 pb-2 text-neutral-900 dark:text-neutral-100">Personal & Academic Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Full Name *</label>
                            <input v-model="form.full_name" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.full_name" class="text-xs text-red-500">{{ form.errors.full_name }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Designation *</label>
                            <input v-model="form.designation" type="text" placeholder="e.g. Senior Teacher" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.designation" class="text-xs text-red-500">{{ form.errors.designation }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email *</label>
                            <input v-model="form.email" type="email" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Mobile Number *</label>
                            <input v-model="form.mobile" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.mobile" class="text-xs text-red-500">{{ form.errors.mobile }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Date of Birth *</label>
                            <input v-model="form.dob" type="date" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.dob" class="text-xs text-red-500">{{ form.errors.dob }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Date of Joining *</label>
                            <input v-model="form.date_of_joining" type="date" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.date_of_joining" class="text-xs text-red-500">{{ form.errors.date_of_joining }}</span>
                        </div>
                    </div>
                </div>

                <!-- Teacher Photo Upload -->
                <div>
                    <label class="block text-sm font-medium mb-1">Profile Photo</label>
                    <div class="mt-2 flex items-center gap-4">
                        <div class="h-20 w-20 rounded-lg bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 overflow-hidden flex items-center justify-center">
                            <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                            <img v-else-if="props.teacher?.photo_path" :src="`/storage/${props.teacher.photo_path}`" class="h-full w-full object-cover" />
                            <span v-else class="text-xs text-neutral-400">No Photo</span>
                        </div>
                        <input type="file" accept="image/*" @change="selectPhoto" class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-neutral-100 file:text-neutral-700 hover:file:bg-neutral-200 dark:file:bg-neutral-800 dark:file:text-neutral-300" />
                    </div>
                    <span v-if="form.errors.photo" class="text-xs text-red-500">{{ form.errors.photo }}</span>
                </div>

                <!-- Qualifications & Address -->
                <div>
                    <label class="block text-sm font-medium mb-1">Academic Qualifications *</label>
                    <textarea v-model="form.qualifications" placeholder="e.g. B.Sc. in Mathematics, B.Ed." rows="2" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"></textarea>
                    <span v-if="form.errors.qualifications" class="text-xs text-red-500 block mt-1">{{ form.errors.qualifications }}</span>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Home Address *</label>
                    <textarea v-model="form.address" rows="2" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"></textarea>
                    <span v-if="form.errors.address" class="text-xs text-red-500 block mt-1">{{ form.errors.address }}</span>
                </div>

                <!-- Assignment Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Subject Checkbox Matrix -->
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-lg">
                        <label class="block text-sm font-bold mb-2">Subject Assignment *</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="sub in availableSubjects" :key="sub" class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                <input type="checkbox" :value="sub" v-model="form.subjects" />
                                <span>{{ sub }}</span>
                            </label>
                        </div>
                        <span v-if="form.errors.subjects" class="text-xs text-red-500 block mt-2">{{ form.errors.subjects }}</span>
                    </div>

                    <!-- Classes Checkbox Matrix -->
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-lg">
                        <label class="block text-sm font-bold mb-2">Class Assignment *</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="cl in availableClasses" :key="cl" class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                <input type="checkbox" :value="cl" v-model="form.classes" />
                                <span>{{ cl }}</span>
                            </label>
                        </div>
                        <span v-if="form.errors.classes" class="text-xs text-red-500 block mt-2">{{ form.errors.classes }}</span>
                    </div>
                </div>

                <!-- Salary Structure -->
                <div>
                    <h2 class="text-lg font-bold border-b border-neutral-100 dark:border-neutral-800 pb-2 text-neutral-900 dark:text-neutral-100">Salary Structure</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Base Salary *</label>
                            <input v-model.number="form.salary_structure.base_salary" type="number" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors['salary_structure.base_salary']" class="text-xs text-red-500">{{ form.errors['salary_structure.base_salary'] }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Allowances *</label>
                            <input v-model.number="form.salary_structure.allowances" type="number" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors['salary_structure.allowances']" class="text-xs text-red-500">{{ form.errors['salary_structure.allowances'] }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Deductions *</label>
                            <input v-model.number="form.salary_structure.deductions" type="number" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors['salary_structure.deductions']" class="text-xs text-red-500">{{ form.errors['salary_structure.deductions'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Form Submit Buttons -->
                <div class="border-t border-neutral-100 dark:border-neutral-800 pt-4 flex justify-end gap-3">
                    <Link href="/teachers" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 text-sm font-semibold rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 hover:bg-neutral-800 text-sm font-semibold rounded-lg text-white shadow">
                        {{ isEdit ? 'Save Changes' : 'Record Profile' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
