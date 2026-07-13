<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Banner {
    id: number;
    title: string;
    subtitle: string | null;
    paragraph: string | null;
    button_text: string | null;
    button_url: string | null;
    bg_color: 'forest' | 'ink' | 'brass';
    image_path: string | null;
    sort_order: number;
    is_active: boolean;
}

const props = defineProps<{ banner: Banner | null }>();

const isEdit = props.banner !== null;

// Form state (using router.post with FormData for file upload)
const form = ref({
    title:        props.banner?.title        ?? '',
    subtitle:     props.banner?.subtitle     ?? '',
    paragraph:    props.banner?.paragraph    ?? '',
    button_text:  props.banner?.button_text  ?? '',
    button_url:   props.banner?.button_url   ?? '',
    bg_color:     props.banner?.bg_color     ?? 'forest',
    sort_order:   props.banner?.sort_order   ?? 0,
    is_active:    props.banner?.is_active    ?? true,
    remove_image: false,
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

// Image state
const imageFile    = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const existingImage = computed(() =>
    props.banner?.image_path
        ? `/storage/${props.banner.image_path}`
        : null
);

function onImageChange(e: Event) {
    const target = e.target as HTMLInputElement;
    const file   = target.files?.[0] ?? null;
    imageFile.value    = file;
    form.value.remove_image = false;
    if (file) {
        const reader = new FileReader();
        reader.onload = (ev) => { imagePreview.value = ev.target?.result as string; };
        reader.readAsDataURL(file);
    } else {
        imagePreview.value = null;
    }
}

function clearImage() {
    imageFile.value    = null;
    imagePreview.value = null;
    form.value.remove_image = true;
    const input = document.getElementById('image-input') as HTMLInputElement;
    if (input) input.value = '';
}

const bgOptions = [
    { value: 'forest', label: '🌿 Forest Green', color: '#1F4D3A' },
    { value: 'ink',    label: '🌑 Dark Ink',     color: '#14213D' },
    { value: 'brass',  label: '🟡 Brass Gold',   color: '#8a6520' },
];

const previewBg = computed(() => {
    if (imagePreview.value || (existingImage.value && !form.value.remove_image)) return 'transparent';
    const map: Record<string, string> = {
        forest: '#1F4D3A',
        ink:    '#14213D',
        brass:  '#8a6520',
    };
    return map[form.value.bg_color] ?? '#1F4D3A';
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Hero Banners', href: '/banners' },
    { title: isEdit ? 'Edit Banner' : 'Add Banner', href: '#' },
];

function submit() {
    processing.value = true;
    errors.value = {};

    const data = new FormData();
    data.append('title',        form.value.title);
    data.append('subtitle',     form.value.subtitle);
    data.append('paragraph',    form.value.paragraph);
    data.append('button_text',  form.value.button_text);
    data.append('button_url',   form.value.button_url);
    data.append('bg_color',     form.value.bg_color);
    data.append('sort_order',   String(form.value.sort_order));
    data.append('is_active',    form.value.is_active ? '1' : '0');
    data.append('remove_image', form.value.remove_image ? '1' : '0');
    if (imageFile.value) {
        data.append('image', imageFile.value);
    }

    const url = isEdit ? `/banners/${props.banner!.id}` : '/banners';

    if (isEdit) {
        data.append('_method', 'PUT');
    }

    router.post(url, data, {
        forceFormData: true,
        onError: (errs) => { errors.value = errs; processing.value = false; },
        onFinish: () => { processing.value = false; },
    });
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Banner' : 'Add Banner'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-3xl mx-auto space-y-8">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">
                    {{ isEdit ? 'Edit Banner' : 'Add New Banner' }}
                </h1>
                <p class="mt-1 text-sm text-neutral-500">This banner appears in the hero slider on the public homepage.</p>
            </div>

            <!-- ── Live Preview ─────────────────────────────────────────── -->
            <div
                class="rounded-xl overflow-hidden relative min-h-[180px] flex items-end"
                :style="{
                    background: previewBg,
                    backgroundImage: (imagePreview || (existingImage && !form.remove_image))
                        ? `url(${imagePreview ?? existingImage})`
                        : 'none',
                    backgroundSize: 'cover',
                    backgroundPosition: 'center',
                }"
            >
                <!-- Dark overlay when image is present -->
                <div
                    v-if="imagePreview || (existingImage && !form.remove_image)"
                    class="absolute inset-0"
                    style="background: linear-gradient(to top, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.15) 60%, transparent 100%)"
                ></div>
                <div class="relative z-10 p-6 text-white w-full">
                    <div class="text-xs font-mono uppercase tracking-widest text-yellow-400 mb-1">Notice Board · Preview</div>
                    <div class="text-xl font-bold mb-1" style="font-family: Georgia, serif; text-shadow: 0 2px 6px rgba(0,0,0,0.4)">
                        {{ form.title || 'Banner Title Here' }}
                    </div>
                    <div v-if="form.subtitle" class="text-sm font-medium opacity-80 mb-1">{{ form.subtitle }}</div>
                    <div v-if="form.paragraph" class="text-xs opacity-70 max-w-lg">{{ form.paragraph }}</div>
                    <span v-if="form.button_text" class="inline-block mt-3 px-4 py-1.5 bg-yellow-500 text-yellow-900 font-bold rounded-full text-sm">
                        {{ form.button_text }}
                    </span>
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
                        placeholder="e.g. Welcome to Saraswati Vidyaniketan"
                        class="sv-input"
                    />
                    <p v-if="errors.title" class="mt-1 text-xs text-red-600">{{ errors.title }}</p>
                </div>

                <!-- Subtitle -->
                <div>
                    <label class="block text-sm font-semibold mb-1">Subtitle</label>
                    <input v-model="form.subtitle" type="text" placeholder="e.g. EST. 1986 · Excellence in Education" class="sv-input" />
                </div>

                <!-- Paragraph -->
                <div>
                    <label class="block text-sm font-semibold mb-1">Paragraph</label>
                    <textarea v-model="form.paragraph" rows="3" placeholder="Write a short description..." class="sv-input resize-none"></textarea>
                </div>

                <!-- Button -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Button Text</label>
                        <input v-model="form.button_text" type="text" placeholder="e.g. Learn More" class="sv-input" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Button URL</label>
                        <input v-model="form.button_url" type="text" placeholder="/notices or https://..." class="sv-input" />
                    </div>
                </div>

                <!-- ── Image Upload ─────────────────────────────────────── -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Background Image <span class="text-neutral-400 font-normal">(optional, replaces color)</span></label>

                    <!-- Current image -->
                    <div v-if="existingImage && !form.remove_image && !imagePreview" class="mb-3 flex items-center gap-3 p-3 bg-neutral-50 dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700">
                        <img :src="existingImage" alt="Current banner" class="h-16 w-28 object-cover rounded-md border border-neutral-300" />
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">Current image</p>
                            <p class="text-xs text-neutral-400">{{ banner?.image_path }}</p>
                        </div>
                        <button type="button" @click="clearImage" class="text-xs font-bold text-red-600 hover:underline px-3 py-1 rounded border border-red-200 hover:bg-red-50">Remove</button>
                    </div>

                    <!-- New image preview -->
                    <div v-if="imagePreview" class="mb-3 flex items-center gap-3 p-3 bg-neutral-50 dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700">
                        <img :src="imagePreview" alt="New banner preview" class="h-16 w-28 object-cover rounded-md border border-neutral-300" />
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">New image selected</p>
                            <p class="text-xs text-neutral-400">{{ imageFile?.name }}</p>
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
                            <p class="text-xs text-neutral-400">JPG, PNG, WebP — max 2MB. Will be used as full background.</p>
                        </div>
                    </label>
                    <input
                        id="image-input"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="onImageChange"
                    />
                    <p v-if="errors.image" class="mt-1 text-xs text-red-600">{{ errors.image }}</p>
                </div>

                <!-- BG Color + Sort + Status -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Background Color
                            <span class="text-neutral-400 font-normal text-xs">(used when no image)</span>
                        </label>
                        <div class="flex flex-col gap-2">
                            <label v-for="opt in bgOptions" :key="opt.value" class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" :value="opt.value" v-model="form.bg_color" class="accent-neutral-800" />
                                <span class="w-4 h-4 rounded-full inline-block border border-neutral-300" :style="{ background: opt.color }"></span>
                                <span class="text-sm">{{ opt.label }}</span>
                            </label>
                        </div>
                    </div>

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
                        :disabled="processing"
                        class="px-5 py-2.5 bg-neutral-950 hover:bg-neutral-800 text-white text-sm font-semibold rounded-lg shadow transition disabled:opacity-60"
                    >
                        {{ processing ? 'Saving...' : (isEdit ? 'Update Banner' : 'Create Banner') }}
                    </button>
                    <a href="/banners" class="text-sm text-neutral-500 hover:underline">Cancel</a>
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
