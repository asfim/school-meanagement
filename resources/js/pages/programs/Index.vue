<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';

interface Program {
    id: number;
    name: string;
    code: string | null;
    description: string | null;
    duration_years: number | null;
}

const props = defineProps<{
    programs: Program[];
}>();

const showAddModal = ref(false);
const showEditModal = ref(false);
const editingProgram = ref<Program | null>(null);

const addForm = useForm({
    name: '',
    code: '',
    description: '',
    duration_years: '',
});

const editForm = useForm({
    name: '',
    code: '',
    description: '',
    duration_years: '',
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Academic Programs', href: '/programs' },
];

function submitAdd() {
    addForm.post('/programs', {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
        }
    });
}

function openEditModal(prog: Program) {
    editingProgram.value = prog;
    editForm.name = prog.name;
    editForm.code = prog.code || '';
    editForm.description = prog.description || '';
    editForm.duration_years = prog.duration_years ? String(prog.duration_years) : '';
    showEditModal.value = true;
}

function submitEdit() {
    if (editingProgram.value) {
        editForm.put(`/programs/${editingProgram.value.id}`, {
            onSuccess: () => {
                showEditModal.value = false;
                editingProgram.value = null;
                editForm.reset();
            }
        });
    }
}

function deleteProgram(id: number) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Deleting this program will remove the association from its subjects.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#171717',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/programs/${id}`);
        }
    });
}
</script>

<template>
    <Head title="Academic Programs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-5xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">Academic Programs</h1>
                    <p class="mt-1 text-sm text-neutral-500">Configure study programs and departments offered by the institution.</p>
                </div>
                <div>
                    <button
                        @click="showAddModal = true"
                        class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-950 dark:hover:bg-neutral-200 shadow transition"
                    >
                        Add New Program
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="prog in programs"
                    :key="prog.id"
                    class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-5 shadow-xs flex flex-col justify-between hover:shadow-md transition"
                >
                    <div>
                        <span class="text-xs font-mono font-bold text-neutral-450 tracking-wider block">{{ prog.code || 'NO-CODE' }}</span>
                        <h3 class="text-lg font-extrabold text-neutral-950 dark:text-neutral-50 mt-1 leading-snug">{{ prog.name }}</h3>
                        <p v-if="prog.description" class="text-sm text-neutral-500 mt-2 line-clamp-2">{{ prog.description }}</p>
                        <p v-if="prog.duration_years" class="text-xs text-neutral-500 mt-2">Duration: {{ prog.duration_years }} year(s)</p>
                    </div>

                    <div class="mt-6 pt-3 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3 text-xs">
                        <button @click="openEditModal(prog)" class="text-neutral-900 dark:text-neutral-100 hover:underline font-bold">Edit</button>
                        <button @click="deleteProgram(prog.id)" class="text-red-650 hover:underline">Delete</button>
                    </div>
                </div>

                <div v-if="programs.length === 0" class="col-span-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-12 text-center text-neutral-500">
                    No programs registered. Click "Add New Program" to begin.
                </div>
            </div>
        </div>

        <!-- Add Program Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-w-sm w-full rounded-xl p-6 space-y-4 shadow-xl">
                <div>
                    <h3 class="text-lg font-black text-neutral-950 dark:text-neutral-50">Add New Program</h3>
                    <p class="text-xs text-neutral-450">Define a new academic program or department.</p>
                </div>
                <form @submit.prevent="submitAdd" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Program Name *</label>
                        <input
                            v-model="addForm.name"
                            type="text"
                            placeholder="e.g. Computer Science"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                        <span v-if="addForm.errors.name" class="text-xs text-red-500 block mt-1">{{ addForm.errors.name }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Program Code</label>
                        <input
                            v-model="addForm.code"
                            type="text"
                            placeholder="e.g. CSE"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                        <span v-if="addForm.errors.code" class="text-xs text-red-500 block mt-1">{{ addForm.errors.code }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Duration (Years)</label>
                        <input
                            v-model="addForm.duration_years"
                            type="number"
                            placeholder="e.g. 4"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                        <span v-if="addForm.errors.duration_years" class="text-xs text-red-500 block mt-1">{{ addForm.errors.duration_years }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Description</label>
                        <textarea
                            v-model="addForm.description"
                            rows="3"
                            placeholder="Brief description of the program..."
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        ></textarea>
                        <span v-if="addForm.errors.description" class="text-xs text-red-500 block mt-1">{{ addForm.errors.description }}</span>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-sm text-neutral-700 dark:text-neutral-300">Cancel</button>
                        <button type="submit" :disabled="addForm.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 text-white hover:bg-neutral-800 rounded-lg text-sm font-semibold shadow">Add Program</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Program Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 max-w-sm w-full rounded-xl p-6 space-y-4 shadow-xl">
                <div>
                    <h3 class="text-lg font-black text-neutral-950 dark:text-neutral-50">Edit Program</h3>
                    <p class="text-xs text-neutral-450">Update program details.</p>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Program Name *</label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                        <span v-if="editForm.errors.name" class="text-xs text-red-500 block mt-1">{{ editForm.errors.name }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Program Code</label>
                        <input
                            v-model="editForm.code"
                            type="text"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                        <span v-if="editForm.errors.code" class="text-xs text-red-500 block mt-1">{{ editForm.errors.code }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Duration (Years)</label>
                        <input
                            v-model="editForm.duration_years"
                            type="number"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        />
                        <span v-if="editForm.errors.duration_years" class="text-xs text-red-500 block mt-1">{{ editForm.errors.duration_years }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Description</label>
                        <textarea
                            v-model="editForm.description"
                            rows="3"
                            class="w-full rounded border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"
                        ></textarea>
                        <span v-if="editForm.errors.description" class="text-xs text-red-500 block mt-1">{{ editForm.errors.description }}</span>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3">
                        <button type="button" @click="showEditModal = false; editingProgram = null" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-sm text-neutral-700 dark:text-neutral-300">Cancel</button>
                        <button type="submit" :disabled="editForm.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 text-white hover:bg-neutral-800 rounded-lg text-sm font-semibold shadow">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
