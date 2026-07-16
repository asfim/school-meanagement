<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Settings {
    id: number;
    institute_name: string;
    tagline: string;
    logo_path: string | null;
    favicon_path: string | null;
    about_title: string | null;
    about_description: string | null;
    about_stats: Array<{ label: string; value: string }> | null;
}

const props = defineProps<{
    settings: Settings;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Site settings',
        href: '/settings/site-settings',
    },
];

const form = useForm({
    institute_name: props.settings.institute_name,
    tagline: props.settings.tagline,
    logo: null as File | null,
    favicon: null as File | null,
    remove_logo: false,
    remove_favicon: false,
    about_title: props.settings.about_title ?? '',
    about_description: props.settings.about_description ?? '',
    about_stats: props.settings.about_stats ?? [
        { label: 'Students', value: '1850+' },
        { label: 'Teachers', value: '96' },
        { label: 'Pass Rate', value: '98%' },
        { label: 'Years of Experience', value: '40+' },
    ] as Array<{ label: string; value: string }>,
});

const logoPreview = ref<string | null>(
    props.settings.logo_path ? `/storage/${props.settings.logo_path}` : null
);
const faviconPreview = ref<string | null>(
    props.settings.favicon_path ? `/storage/${props.settings.favicon_path}` : null
);

function onLogoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] || null;
    form.logo = file;
    form.remove_logo = false;
    if (file) {
        logoPreview.value = URL.createObjectURL(file);
    } else {
        logoPreview.value = props.settings.logo_path ? `/storage/${props.settings.logo_path}` : null;
    }
}

function onFaviconChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] || null;
    form.favicon = file;
    form.remove_favicon = false;
    if (file) {
        faviconPreview.value = URL.createObjectURL(file);
    } else {
        faviconPreview.value = props.settings.favicon_path ? `/storage/${props.settings.favicon_path}` : null;
    }
}

function removeLogo() {
    form.logo = null;
    form.remove_logo = true;
    logoPreview.value = null;
    const input = document.getElementById('logo-input') as HTMLInputElement;
    if (input) input.value = '';
}

function removeFavicon() {
    form.favicon = null;
    form.remove_favicon = true;
    faviconPreview.value = null;
    const input = document.getElementById('favicon-input') as HTMLInputElement;
    if (input) input.value = '';
}

const submit = () => {
    form.post(route('site-settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Site settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Site settings" description="Manage logo, institute name, tagline, and favicon" />

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Institute Name -->
                    <div class="grid gap-2">
                        <Label for="institute_name">Institute Name</Label>
                        <Input
                            id="institute_name"
                            class="mt-1 block w-full"
                            v-model="form.institute_name"
                            required
                            placeholder="e.g. Saraswati Vidyaniketan"
                        />
                        <InputError class="mt-2" :message="form.errors.institute_name" />
                    </div>

                    <!-- Tagline (EST. 1986 · DHAKA) -->
                    <div class="grid gap-2">
                        <Label for="tagline">Tagline / Establishment Text</Label>
                        <Input
                            id="tagline"
                            class="mt-1 block w-full"
                            v-model="form.tagline"
                            required
                            placeholder="e.g. EST. 1986 · DHAKA"
                        />
                        <InputError class="mt-2" :message="form.errors.tagline" />
                    </div>

                    <!-- Logo Upload -->
                    <div class="grid gap-2">
                        <Label>Institute Logo</Label>
                        <div class="flex items-center gap-4 mt-1">
                            <div
                                class="w-16 h-16 rounded-lg bg-neutral-100 dark:bg-neutral-800 border flex items-center justify-center overflow-hidden"
                            >
                                <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-contain" alt="Logo preview" />
                                <span v-else class="text-xs text-neutral-400">No Logo</span>
                            </div>
                            <div class="flex flex-col gap-2">
                                <input
                                    id="logo-input"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="onLogoChange"
                                />
                                <div class="flex gap-2">
                                    <Button type="button" variant="outline" size="sm" as-child>
                                        <label for="logo-input" class="cursor-pointer">Upload Logo</label>
                                    </Button>
                                    <Button v-if="logoPreview" type="button" variant="destructive" size="sm" @click="removeLogo">
                                        Remove
                                    </Button>
                                </div>
                                <span class="text-xs text-neutral-400">PNG, JPG, WebP — max 2MB</span>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.logo" />
                    </div>

                    <!-- Favicon Upload -->
                    <div class="grid gap-2">
                        <Label>Favicon</Label>
                        <div class="flex items-center gap-4 mt-1">
                            <div
                                class="w-10 h-10 rounded-lg bg-neutral-100 dark:bg-neutral-800 border flex items-center justify-center overflow-hidden"
                            >
                                <img v-if="faviconPreview" :src="faviconPreview" class="w-full h-full object-contain" alt="Favicon preview" />
                                <span v-else class="text-xs text-neutral-400">No Icon</span>
                            </div>
                            <div class="flex flex-col gap-2">
                                <input
                                    id="favicon-input"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="onFaviconChange"
                                />
                                <div class="flex gap-2">
                                    <Button type="button" variant="outline" size="sm" as-child>
                                        <label for="favicon-input" class="cursor-pointer">Upload Favicon</label>
                                    </Button>
                                    <Button v-if="faviconPreview" type="button" variant="destructive" size="sm" @click="removeFavicon">
                                        Remove
                                    </Button>
                                </div>
                                <span class="text-xs text-neutral-400">ICO, PNG, WebP — max 512KB</span>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.favicon" />
                    </div>

                    <!-- Save Action -->
                    <div class="flex items-center gap-4 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                        <Button :disabled="form.processing">Save Settings</Button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-neutral-600">Saved successfully.</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
