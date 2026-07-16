<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';

interface Notice {
    id: number;
    title: string;
    description: string;
    category: 'exam' | 'holiday' | 'event' | 'general' | 'admission' | 'urgent';
    attachment_path: string | null;
    image_path: string | null;
    publish_date: string;
    expiry_date: string | null;
    target_audience: 'all' | 'students' | 'teachers' | 'parents';
    status: 'active' | 'inactive' | 'draft';
}

const props = defineProps<{
    notice: Notice | null;
}>();

const isEdit = !!props.notice;

const form = useForm({
    _method: isEdit ? 'PUT' : 'POST',
    title: props.notice?.title || '',
    description: props.notice?.description || '',
    category: props.notice?.category || 'general',
    publish_date: props.notice ? new Date(props.notice.publish_date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
    expiry_date: props.notice && props.notice.expiry_date ? new Date(props.notice.expiry_date).toISOString().split('T')[0] : '',
    target_audience: props.notice?.target_audience || 'all',
    status: props.notice?.status || 'active',
    attachment: null as File | null,
    featured_image: null as File | null,
    remove_image: false,
});

const imagePreview = ref<string | null>(
    props.notice?.image_path ? `/storage/${props.notice.image_path}` : null
);

function onImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] || null;
    form.featured_image = file;
    form.remove_image = false;
    if (file) {
        imagePreview.value = URL.createObjectURL(file);
    } else {
        imagePreview.value = props.notice?.image_path ? `/storage/${props.notice.image_path}` : null;
    }
}

function removeImage() {
    form.featured_image = null;
    form.remove_image = true;
    imagePreview.value = null;
    const input = document.getElementById('image-input') as HTMLInputElement;
    if (input) {
        input.value = '';
    }
}

function submit() {
    if (isEdit) {
        form.post(`/notices/${props.notice?.id}`, {
            onSuccess: () => {},
        });
    } else {
        form.post('/notices', {
            onSuccess: () => {},
        });
    }
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Notice Board', href: '/notices' },
    { title: isEdit ? 'Edit Notice' : 'Create Notice', href: '#' },
];
</script>

<template>
    <Head :title="isEdit ? 'Edit School Notice' : 'Draft School Notice'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-3xl mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-neutral-900 dark:text-neutral-50">{{ isEdit ? 'Edit Notice' : 'Create Notice' }}</h1>
                    <p class="text-sm text-neutral-500">Post announcements, holiday schedules, and urgent notifications to the school board.</p>
                </div>
                <Link href="/notices" class="text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:underline">&larr; Back to Board</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm space-y-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Notice Title *</label>
                    <input v-model="form.title" type="text" placeholder="e.g. First Term Exam Schedule 2026" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                    <span v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</span>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Description / details *</label>
                    <textarea v-model="form.description" rows="6" placeholder="Provide full details of the notice..." class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none"></textarea>
                    <span v-if="form.errors.description" class="text-xs text-red-500">{{ form.errors.description }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Category *</label>
                        <select v-model="form.category" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                            <option value="general">General Circular</option>
                            <option value="exam">Exam Schedule</option>
                            <option value="holiday">Holiday Notice</option>
                            <option value="event">School Event</option>
                            <option value="admission">Admissions Notice</option>
                            <option value="urgent">Urgent Announcement</option>
                        </select>
                        <span v-if="form.errors.category" class="text-xs text-red-500">{{ form.errors.category }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Publish Date *</label>
                        <input v-model="form.publish_date" type="date" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                        <span v-if="form.errors.publish_date" class="text-xs text-red-500">{{ form.errors.publish_date }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Expiry Date (Auto-hide)</label>
                        <input v-model="form.expiry_date" type="date" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none" />
                        <span v-if="form.errors.expiry_date" class="text-xs text-red-500">{{ form.errors.expiry_date }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Publishing Status *</label>
                        <select v-model="form.status" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none">
                            <option value="active">Active (Visible)</option>
                            <option value="draft">Draft (Private)</option>
                            <option value="inactive">Inactive (Hidden)</option>
                        </select>
                        <span v-if="form.errors.status" class="text-xs text-red-500">{{ form.errors.status }}</span>
                    </div>
                </div>

                <!-- Attachment and Featured Image fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-neutral-100 dark:border-neutral-800 pt-6">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Upload Attachment (PDF or Image)</label>
                        <input
                            type="file"
                            accept=".pdf,image/*"
                            class="w-full text-sm text-neutral-500 border border-neutral-300 dark:border-neutral-700 rounded-lg p-2 bg-neutral-50 dark:bg-neutral-950"
                            @change="(e) => form.attachment = (e.target as HTMLInputElement).files?.[0] || null"
                        />
                        <span v-if="notice?.attachment_path" class="text-xs text-neutral-400 block mt-1">
                            Current file: <a :href="'/storage/' + notice.attachment_path" target="_blank" class="text-neutral-700 dark:text-neutral-300 underline font-medium">View uploaded file</a>
                        </span>
                        <span v-if="form.errors.attachment" class="text-xs text-red-500 block mt-1">{{ form.errors.attachment }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Featured Image</label>
                        <div class="flex items-center gap-4">
                            <div v-if="imagePreview" class="w-16 h-16 rounded-lg border overflow-hidden bg-neutral-100 dark:bg-neutral-800 flex-shrink-0 flex items-center justify-center">
                                <img :src="imagePreview" class="w-full h-full object-cover" />
                            </div>
                            <div class="flex-1 space-y-2">
                                <input
                                    id="image-input"
                                    type="file"
                                    accept="image/*"
                                    class="w-full text-sm text-neutral-500 border border-neutral-300 dark:border-neutral-700 rounded-lg p-2 bg-neutral-50 dark:bg-neutral-950"
                                    @change="onImageChange"
                                />
                                <div v-if="imagePreview" class="flex gap-2">
                                    <button type="button" @click="removeImage" class="text-xs text-red-500 hover:underline">Remove Image</button>
                                </div>
                            </div>
                        </div>
                        <span v-if="form.errors.featured_image" class="text-xs text-red-500 block mt-1">{{ form.errors.featured_image }}</span>
                    </div>
                </div>

                <div class="border-t border-neutral-100 dark:border-neutral-800 pt-4 flex justify-end gap-3">
                    <Link href="/notices" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 text-sm font-semibold rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-neutral-950 dark:bg-neutral-50 dark:text-neutral-950 hover:bg-neutral-800 text-sm font-semibold rounded-lg text-white shadow">
                        {{ isEdit ? 'Save Changes' : 'Publish Notice' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
