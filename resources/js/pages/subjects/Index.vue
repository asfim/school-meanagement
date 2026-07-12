<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

interface Subject {
    id: number;
    name: string;
    code: string | null;
}

const props = defineProps<{
    subjects: Subject[];
}>();

const showAddModal = ref(false);
const showEditModal = ref(false);
const editingSubject = ref<Subject | null>(null);

const addForm = useForm({
    name: '',
    code: '',
});

const editForm = useForm({
    name: '',
    code: '',
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Subjects', href: '/subjects' },
];

function submitAdd() {
    addForm.post('/subjects', {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
        }
    });
}

function openEditModal(sub: Subject) {
    editingSubject.value = sub;
    editForm.name = sub.name;
    editForm.code = sub.code || '';
    showEditModal.value = true;
}

function submitEdit() {
    if (editingSubject.value) {
        editForm.put(`/subjects/${editingSubject.value.id}`, {
            onSuccess: () => {
                showEditModal.value = false;
                editingSubject.value = null;
                editForm.reset();
            }
        });
    }
}

function deleteSubject(id: number) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Are you sure you want to delete this subject? Academic results and teacher assignments using it might be affected.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#171717',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/subjects/${id}`);
        }
    });
}
</script>

<template>
    <Head title="Subjects Registry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-5xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">Subjects Registry</h1>
                    <p class="mt-1 text-sm text-neutral-500">Configure and manage academic course subjects taught in classes.</p>
                </div>
                <div>
                    <button
                        @click="showAddModal = true"
                        class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200 shadow transition"
                    >
                        Add New Subject
                    </button>
                </div>
            </div>

            <!-- Subject Grid list -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="sub in subjects"
                    :key="sub.id"
                    class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-5 shadow-xs flex flex-col justify-between hover:shadow-md transition"
                >
                    <div>
                        <span class="text-xs font-mono font-bold text-neutral-450 tracking-wider block">{{ sub.code || 'NO-CODE' }}</span>
                        <h3 class="text-lg font-extrabold text-neutral-950 dark:text-neutral-50 mt-1 leading-snug">{{ sub.name }}</h3>
                    </div>

                    <div class="mt-6 pt-3 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3 text-xs">
                        <button @click="openEditModal(sub)" class="text-neutral-900 dark:text-neutral-100 hover:underline font-bold">Edit</button>
                        <button @click="deleteSubject(sub.id)" class="text-red-650 hover:underline">Delete</button>
                    </div>
                </div>

                <div v-if="subjects.length === 0" class="col-span-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-12 text-center text-neutral-500">
                    No subjects registered in system. Click "Add New Subject" to begin.
                </div>
            </div>
        </div>

        <!-- Add Subject Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-w-sm w-full rounded-xl p-6 space-y-4 shadow-xl">
                <div>
                    <h3 class="text-lg font-black text-neutral-950 dark:text-neutral-50">Add New Subject</h3>
                    <p class="text-xs text-neutral-450">Define name and curriculum code for the course.</p>
                </div>
                <form @submit.prevent="submitAdd" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Subject Name *</label>
                        <input
                            v-model="addForm.name"
                            type="text"
                            placeholder="e.g. Higher Mathematics"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                        <span v-if="addForm.errors.name" class="text-xs text-red-500 block mt-1">{{ addForm.errors.name }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Subject Code</label>
                        <input
                            v-model="addForm.code"
                            type="text"
                            placeholder="e.g. MATH201"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                        <span v-if="addForm.errors.code" class="text-xs text-red-500 block mt-1">{{ addForm.errors.code }}</span>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-sm text-neutral-700 dark:text-neutral-300">Cancel</button>
                        <button type="submit" :disabled="addForm.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 text-white hover:bg-neutral-800 rounded-lg text-sm font-semibold shadow">Add Subject</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Subject Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-w-sm w-full rounded-xl p-6 space-y-4 shadow-xl">
                <div>
                    <h3 class="text-lg font-black text-neutral-950 dark:text-neutral-50">Edit Subject Details</h3>
                    <p class="text-xs text-neutral-450">Modify course descriptions or reference code.</p>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Subject Name *</label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                        <span v-if="editForm.errors.name" class="text-xs text-red-500 block mt-1">{{ editForm.errors.name }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Subject Code</label>
                        <input
                            v-model="editForm.code"
                            type="text"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                        <span v-if="editForm.errors.code" class="text-xs text-red-500 block mt-1">{{ editForm.errors.code }}</span>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3">
                        <button type="button" @click="showEditModal = false; editingSubject = null" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-sm text-neutral-700 dark:text-neutral-300">Cancel</button>
                        <button type="submit" :disabled="editForm.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 text-white hover:bg-neutral-800 rounded-lg text-sm font-semibold shadow">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
