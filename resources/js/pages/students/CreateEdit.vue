<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Student {
    id: number;
    student_id: string;
    full_name_en: string;
    full_name_native: string;
    dob: string;
    gender: string;
    parent_name: string;
    parent_mobile: string;
    permanent_address: string;
    current_address: string;
    program_name: string;
    signature_path: string | null;
    section: string;
    roll_number: number;
    admission_date: string;
    blood_group: string | null;
    emergency_contact: string;
    status: string;
    photo_path: string | null;
}

interface Program {
    id: number;
    name: string;
    code: string;
}

const props = defineProps<{
    student: Student | null;
    programs: Program[];
}>();

const isEdit = !!props.student;

const sections = ['A', 'B', 'C'];
const bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

const form = useForm({
    _method: isEdit ? 'PUT' : 'POST', // For file uploads inside PUT request we use Laravel method spoofing
    full_name_en: props.student?.full_name_en || '',
    full_name_native: props.student?.full_name_native || '',
    dob: props.student ? new Date(props.student.dob).toISOString().split('T')[0] : '',
    gender: props.student?.gender || 'male',
    parent_name: props.student?.parent_name || '',
    parent_mobile: props.student?.parent_mobile || '',
    permanent_address: props.student?.permanent_address || '',
    current_address: props.student?.current_address || '',
    program_name: props.student?.program_name || '',
    section: props.student?.section || 'A',
    roll_number: props.student?.roll_number || 1,
    admission_date: props.student ? new Date(props.student.admission_date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
    blood_group: props.student?.blood_group || '',
    emergency_contact: props.student?.emergency_contact || '',
    status: props.student?.status || 'active',
    photo: null as File | null,
    signature: null as File | null,
});

const photoPreview = ref<string | null>(null);
const signaturePreview = ref<string | null>(null);

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

function selectSignature(e: Event) {
    const files = (e.target as HTMLInputElement).files;
    if (files && files[0]) {
        form.signature = files[0];
        const reader = new FileReader();
        reader.onload = (event) => {
            signaturePreview.value = event.target?.result as string;
        };
        reader.readAsDataURL(files[0]);
    }
}

function submit() {
    if (isEdit) {
        // Use post with spoofing because files aren't supported in raw PUT
        form.post(`/students/${props.student?.id}`, {
            onSuccess: () => {},
        });
    } else {
        form.post('/students', {
            onSuccess: () => {},
        });
    }
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: isEdit ? 'Edit Student' : 'Register Student', href: '#' },
];
</script>

<template>
    <Head :title="isEdit ? 'Edit Student Profile' : 'Register New Student'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-4xl mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-neutral-900 dark:text-neutral-50">{{ isEdit ? 'Edit Student Details' : 'Student Registration' }}</h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Fill in the fields below to update or register a student profile.</p>
                </div>
                <Link href="/students" class="text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:underline">&larr; Back to Directory</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm space-y-6">
                <!-- Section 1: Academic Information -->
                <div>
                    <h2 class="text-lg font-bold border-b border-neutral-100 dark:border-neutral-800 pb-2 text-neutral-900 dark:text-neutral-100">Academic details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Program *</label>
                            <select v-model="form.program_name" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                                <option value="">Select Program</option>
                                <option v-for="p in programs" :key="p.id" :value="p.name">{{ p.name }}</option>
                            </select>
                            <span v-if="form.errors.program_name" class="text-xs text-red-500">{{ form.errors.program_name }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Section *</label>
                            <select v-model="form.section" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                                <option v-for="s in sections" :key="s" :value="s">Section {{ s }}</option>
                            </select>
                            <span v-if="form.errors.section" class="text-xs text-red-500">{{ form.errors.section }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Roll Number *</label>
                            <input v-model="form.roll_number" type="number" min="1" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.roll_number" class="text-xs text-red-500">{{ form.errors.roll_number }}</span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Student Information -->
                <div>
                    <h2 class="text-lg font-bold border-b border-neutral-100 dark:border-neutral-800 pb-2 text-neutral-900 dark:text-neutral-100">Personal info</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Full Name (English) *</label>
                            <input v-model="form.full_name_en" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.full_name_en" class="text-xs text-red-500">{{ form.errors.full_name_en }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Full Name (Native Language) *</label>
                            <input v-model="form.full_name_native" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.full_name_native" class="text-xs text-red-500">{{ form.errors.full_name_native }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Date of Birth *</label>
                            <input v-model="form.dob" type="date" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.dob" class="text-xs text-red-500">{{ form.errors.dob }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Gender *</label>
                            <div class="flex gap-4 mt-2">
                                <label class="inline-flex items-center gap-1.5"><input type="radio" value="male" v-model="form.gender" /> Male</label>
                                <label class="inline-flex items-center gap-1.5"><input type="radio" value="female" v-model="form.gender" /> Female</label>
                                <label class="inline-flex items-center gap-1.5"><input type="radio" value="other" v-model="form.gender" /> Other</label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Blood Group</label>
                            <select v-model="form.blood_group" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                                <option value="">Select Blood Group</option>
                                <option v-for="bg in bloodGroups" :key="bg" :value="bg">{{ bg }}</option>
                            </select>
                            <span v-if="form.errors.blood_group" class="text-xs text-red-500">{{ form.errors.blood_group }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Admission Date *</label>
                            <input v-model="form.admission_date" type="date" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.admission_date" class="text-xs text-red-500">{{ form.errors.admission_date }}</span>
                        </div>
                    </div>
                </div>

                <!-- Photo & Signature Upload Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-1">Student Photo</label>
                        <div class="mt-2 flex items-center gap-4">
                            <div class="h-24 w-24 shrink-0 rounded-lg bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 overflow-hidden flex items-center justify-center">
                                <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                                <img v-else-if="props.student?.photo_path" :src="`/storage/${props.student.photo_path}`" class="h-full w-full object-cover" />
                                <span v-else class="text-xs text-neutral-400">No Photo</span>
                            </div>
                            <input type="file" accept="image/*" @change="selectPhoto" class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-neutral-100 file:text-neutral-700 hover:file:bg-neutral-200 dark:file:bg-neutral-800 dark:file:text-neutral-300" />
                        </div>
                        <span v-if="form.errors.photo" class="text-xs text-red-500">{{ form.errors.photo }}</span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Authorization Signature</label>
                        <div class="mt-2 flex items-center gap-4">
                            <div class="h-24 w-40 shrink-0 rounded-lg bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 overflow-hidden flex items-center justify-center">
                                <img v-if="signaturePreview" :src="signaturePreview" class="h-full w-full object-contain" />
                                <img v-else-if="props.student?.signature_path" :src="`/storage/${props.student.signature_path}`" class="h-full w-full object-contain" />
                                <span v-else class="text-xs text-neutral-400">No Signature</span>
                            </div>
                            <input type="file" accept="image/*" @change="selectSignature" class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-neutral-100 file:text-neutral-700 hover:file:bg-neutral-200 dark:file:bg-neutral-800 dark:file:text-neutral-300" />
                        </div>
                        <span v-if="form.errors.signature" class="text-xs text-red-500">{{ form.errors.signature }}</span>
                    </div>
                </div>

                <!-- Section 3: Parent & Contact Details -->
                <div>
                    <h2 class="text-lg font-bold border-b border-neutral-100 dark:border-neutral-800 pb-2 text-neutral-900 dark:text-neutral-100">Parent & Contact details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Parent/Guardian Name *</label>
                            <input v-model="form.parent_name" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.parent_name" class="text-xs text-red-500">{{ form.errors.parent_name }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Parent Mobile Number *</label>
                            <input v-model="form.parent_mobile" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.parent_mobile" class="text-xs text-red-500">{{ form.errors.parent_mobile }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Emergency Contact Number *</label>
                            <input v-model="form.emergency_contact" type="text" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                            <span v-if="form.errors.emergency_contact" class="text-xs text-red-500">{{ form.errors.emergency_contact }}</span>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Addresses -->
                <div>
                    <h2 class="text-lg font-bold border-b border-neutral-100 dark:border-neutral-800 pb-2 text-neutral-900 dark:text-neutral-100">Addresses</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Current Address *</label>
                            <textarea v-model="form.current_address" rows="3" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"></textarea>
                            <span v-if="form.errors.current_address" class="text-xs text-red-500">{{ form.errors.current_address }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Permanent Address *</label>
                            <textarea v-model="form.permanent_address" rows="3" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"></textarea>
                            <span v-if="form.errors.permanent_address" class="text-xs text-red-500">{{ form.errors.permanent_address }}</span>
                        </div>
                    </div>
                </div>

                <!-- Status (Only shown in edit mode) -->
                <div v-if="isEdit">
                    <label class="block text-sm font-medium mb-1">Enrollment Status</label>
                    <select v-model="form.status" class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                        <option value="active">Active</option>
                        <option value="transferred">Transferred</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Form Submit Buttons -->
                <div class="border-t border-neutral-100 dark:border-neutral-800 pt-4 flex justify-end gap-3">
                    <Link href="/students" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 text-sm font-semibold rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 hover:bg-neutral-800 text-sm font-semibold rounded-lg text-white shadow">
                        {{ isEdit ? 'Save Changes' : 'Submit Registration' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
