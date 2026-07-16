<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, useForm } from '@inertiajs/vue3';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Settings {
    id: number;
    about_title: string | null;
    about_description: string | null;
    about_stats: Array<{ label: string; value: string }> | null;
}

const props = defineProps<{
    settings: Settings;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'About Us',
        href: '/about-us',
    },
];

const form = useForm({
    about_title: props.settings.about_title ?? '',
    about_description: props.settings.about_description ?? '',
    about_stats: props.settings.about_stats ?? [
        { label: 'Students', value: '1850+' },
        { label: 'Teachers', value: '96' },
        { label: 'Pass Rate', value: '98%' },
        { label: 'Years of Experience', value: '40+' },
    ] as Array<{ label: string; value: string }>,
});

const submit = () => {
    form.post(route('about-us.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="About Us Page settings" />

        <div class="p-6 max-w-4xl mx-auto space-y-6">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm">
                <HeadingSmall title="About Us Content" description="Configure the About Us content displayed on the public website" class="mb-6" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="about_title">About Us Title</Label>
                        <Input
                            id="about_title"
                            class="mt-1 block w-full"
                            v-model="form.about_title"
                            required
                            placeholder="e.g. 40 Years of Education, Discipline &amp; Values"
                        />
                        <InputError class="mt-2" :message="form.errors.about_title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="about_description">About Us Description</Label>
                        <textarea
                            id="about_description"
                            class="mt-1 block w-full min-h-[150px] rounded-md border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-400 dark:focus:ring-neutral-500"
                            v-model="form.about_description"
                            required
                            placeholder="Brief description about the institution..."
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.about_description" />
                    </div>

                    <!-- Stats -->
                    <div class="grid gap-3">
                        <Label>Statistics (up to 4 stats)</Label>
                        <div class="space-y-2">
                            <div
                                v-for="(stat, index) in form.about_stats"
                                :key="index"
                                class="flex gap-3 items-center"
                            >
                                <Input v-model="stat.value" placeholder="e.g. 1850+" class="w-32 flex-shrink-0" required />
                                <Input v-model="stat.label" placeholder="e.g. Students" class="flex-1" required />
                                <button
                                    type="button"
                                    @click="form.about_stats.splice(index, 1)"
                                    class="text-red-500 hover:text-red-700 text-sm font-semibold px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-950/20 transition"
                                >Remove</button>
                            </div>
                        </div>
                        <button
                            v-if="form.about_stats.length < 4"
                            type="button"
                            @click="form.about_stats.push({ label: '', value: '' })"
                            class="w-full text-sm font-semibold text-neutral-500 border border-dashed border-neutral-300 dark:border-neutral-600 rounded-md py-2 hover:border-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 transition"
                        >+ Add Stat</button>
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
        </div>
    </AppLayout>
</template>
