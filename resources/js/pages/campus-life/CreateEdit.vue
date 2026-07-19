<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface CampusLifeItem {
    id: number;
    title: string;
    description: string | null;
    image_path: string | null;
    sort_order: number;
    is_active: boolean;
}

const props = defineProps<{ item: CampusLifeItem | null }>();

const isEdit = props.item !== null;

const form = useForm({
    _method: isEdit ? 'PUT' : 'POST',
    title:       props.item?.title       ?? '',
    description: props.item?.description ?? '',
    sort_order:  props.item?.sort_order  ?? 0,
    is_active:   props.item?.is_active   ?? true,
    remove_image: false,
    image:        null as File | null,
});

// Image state
const imagePreview = ref<string | null>(null);
const existingImage = computed(() =>
    props.item?.image_path
        ? `/storage/${props.item.image_path}`
        : null
);

function onImageChange(e: Event) {
    const target = e.target as HTMLInputElement;
    const file   = target.files?.[0] ?? null;
    form.image         = file;
    form.remove_image = false;
    if (file) {
        const reader = new FileReader();
        reader.onload = (ev) => { imagePreview.value = ev.target?.result as string; };
        reader.readAsDataURL(file);
    } else {
        imagePreview.value = null;
    }
}

function clearImage() {
    form.image         = null;
    imagePreview.value = null;
    form.remove_image = true;
    const input = document.getElementById('image-input') as HTMLInputElement;
    if (input) input.value = '';
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Campus Life Gallery', href: '/campus-life' },
    { title: isEdit ? 'Edit Item' : 'Add Item', href: '#' },
];

function submit() {
    const url = isEdit ? `/campus-life/${props.item!.id}` : '/campus-life';

    form.transform((data) => ({
        ...data,
        is_active: data.is_active ? 1 : 0,
        remove_image: data.remove_image ? 1 : 0,
    })).post(url, {
        onSuccess: () => {},
    });
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Gallery Item' : 'Add Gallery Item'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-3xl mx-auto space-y-8">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">
                    {{ isEdit ? 'Edit Gallery Item' : 'Add Gallery Item' }}
                </h1>
                <p class="mt-1 text-sm text-neutral-500">This item appears in the Campus Life gallery on the public homepage.</p>
            </div>

            <!-- ── Live Preview ─────────────────────────────────────────── -->
            <div
                class="rounded-xl overflow-hidden relative min-h-[180px] flex items-end bg-[#14213d] border border-neutral-800"
            >
                <div class="absolute inset-0 w-full h-full flex items-center justify-center overflow-hidden">
                    <img
                        v-if="imagePreview || (existingImage && !form.remove_image)"
                        :src="imagePreview ?? existingImage ?? ''"
                        class="w-full h-full object-contain"
                        alt="Preview"
                    />
                </div>
                <div class="relative z-10 p-6 text-white w-full bg-gradient-to-t from-black/80 via-black/40 to-transparent">
                    <div class="text-xs font-mono uppercase tracking-widest text-yellow-400 mb-1">Campus Life · Preview</div>
                    <div class="text-xl font-bold mb-1" style="font-family: Georgia, serif; text-shadow: 0 2px 6px rgba(0,0,0,0.4)">
                        {{ form.title || 'Moment Title Here' }}
                    </div>
                    <div v-if="form.description" class="text-xs opacity-75 max-w-lg">{{ form.description }}</div>
                </div>
            </div>

            <!-- ── Form ─────────────────────────────────────────────────── -->
            <form @submit.prevent="submit" class="space-y-5 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm">

                <!-- Title -->
                <div>
                    <label class="block text-sm font-semibold mb-1">Title <span class="text-red-500">*</span></label>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="e.g. Annual Sports Day"
                        class="sv-input"
                    />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold mb-1">Description</label>
                    <textarea v-model="form.description" rows="3" placeholder="A short caption for this gallery moment..." class="sv-input resize-none"></textarea>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Gallery Image <span class="text-neutral-400 font-normal">(optional)</span></label>

                    <!-- Current image -->
                    <div v-if="existingImage && !form.remove_image && !imagePreview" class="mb-3 flex items-center gap-3 p-3 bg-neutral-50 dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700">
                        <img :src="existingImage" alt="Current" class="h-16 w-28 object-cover rounded-md border border-neutral-300" />
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">Current image</p>
                            <p class="text-xs text-neutral-400">{{ item?.image_path }}</p>
                        </div>
                        <button type="button" @click="clearImage" class="text-xs font-bold text-red-600 hover:underline px-3 py-1 rounded border border-red-200 hover:bg-red-50">Remove</button>
                    </div>

                    <!-- New image preview -->
                    <div v-if="imagePreview" class="mb-3 flex items-center gap-3 p-3 bg-neutral-50 dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700">
                        <img :src="imagePreview" alt="New preview" class="h-16 w-28 object-cover rounded-md border border-neutral-300" />
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">New image selected</p>
                            <p class="text-xs text-neutral-400">{{ form.image?.name }}</p>
                        </div>
                        <button type="button" @click="clearImage" class="text-xs font-bold text-red-600 hover:underline px-3 py-1 rounded border border-red-200 hover:bg-red-50">Clear</button>
                    </div>

                    <!-- Upload button -->
                    <label
                        for="image-input"
                        class="flex items-center gap-3 cursor-pointer border-2 border-dashed border-neutral-300 dark:border-neutral-700 rounded-xl p-5 hover:border-neutral-500 transition group"
                    >
                        <span class="text-3xl">🖼️</span>
                        <div>
                            <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 group-hover:text-neutral-900">
                                Click to upload image
                            </p>
                            <p class="text-xs text-neutral-400">JPG, PNG, WebP — max 2MB. Used as gallery tile background.</p>
                        </div>
                    </label>
                    <input
                        id="image-input"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="onImageChange"
                    />
                    <p v-if="form.errors.image" class="mt-1 text-xs text-red-600">{{ form.errors.image }}</p>
                </div>

                <!-- Sort + Status -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Sort Order</label>
                        <input
                            v-model.number="form.sort_order"
                            type="number" min="0"
                            class="sv-input"
                        />
                        <p class="text-xs text-neutral-400 mt-1">Lower = shown first</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Status</label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" v-model="form.is_active" class="accent-neutral-900 w-4 h-4" />
                            <span class="text-sm">Active (show on homepage)</span>
                        </label>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex items-center gap-3 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 bg-neutral-950 hover:bg-neutral-800 text-white text-sm font-semibold rounded-lg shadow transition disabled:opacity-60"
                    >
                        {{ form.processing ? 'Saving...' : (isEdit ? 'Update Item' : 'Create Item') }}
                    </button>
                    <Link href="/campus-life" class="text-sm text-neutral-500 hover:underline">Cancel</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.sv-input {
    width: 100%;
    border: 1.5px solid rgba(20,33,61,0.18);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 14px;
    background: #fff;
    color: #14213D;
    outline: none;
    transition: border-color .2s;
    font-family: 'Inter', sans-serif;
    box-sizing: border-box;
}
.sv-input:focus { border-color: #C89B3C; box-shadow: 0 0 0 3px rgba(200,155,60,0.12); }
.dark .sv-input { background: #111; color: #f0f0f0; border-color: rgba(255,255,255,0.15); }
</style>
