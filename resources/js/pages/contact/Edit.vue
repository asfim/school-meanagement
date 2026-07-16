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
    contact_address: string | null;
    contact_phone: string | null;
    contact_email: string | null;
    contact_hours: string | null;
}

const props = defineProps<{
    settings: Settings;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Contact Us',
        href: '/contact-us',
    },
];

const form = useForm({
    contact_address: props.settings.contact_address ?? '124, Mirpur Road, Dhaka — 1207',
    contact_phone: props.settings.contact_phone ?? '02-9876543',
    contact_email: props.settings.contact_email ?? 'info@saraswatividya.edu.bd',
    contact_hours: props.settings.contact_hours ?? "Sat — Thu : 8:00 AM — 4:00 PM\nFriday : Closed",
});

const submit = () => {
    form.post(route('contact-us.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Contact Us Page settings" />

        <div class="p-6 max-w-4xl mx-auto space-y-6">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm">
                <HeadingSmall title="Contact Us Details" description="Configure contact details and office hours displayed on the homepage" class="mb-6" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="contact_address">Address</Label>
                        <Input
                            id="contact_address"
                            class="mt-1 block w-full"
                            v-model="form.contact_address"
                            required
                            placeholder="e.g. 124, Mirpur Road, Dhaka — 1207"
                        />
                        <InputError class="mt-2" :message="form.errors.contact_address" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="contact_phone">Phone Number</Label>
                        <Input
                            id="contact_phone"
                            class="mt-1 block w-full"
                            v-model="form.contact_phone"
                            required
                            placeholder="e.g. 02-9876543"
                        />
                        <InputError class="mt-2" :message="form.errors.contact_phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="contact_email">Email Address</Label>
                        <Input
                            id="contact_email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.contact_email"
                            required
                            placeholder="e.g. info@saraswatividya.edu.bd"
                        />
                        <InputError class="mt-2" :message="form.errors.contact_email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="contact_hours">Office Hours / Days</Label>
                        <textarea
                            id="contact_hours"
                            class="mt-1 block w-full min-h-[120px] rounded-md border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-400 dark:focus:ring-neutral-500"
                            v-model="form.contact_hours"
                            required
                            placeholder="e.g. Sat — Thu : 8:00 AM — 4:00 PM&#10;Friday : Closed"
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.contact_hours" />
                    </div>

                    <!-- Save Action -->
                    <div class="flex items-center gap-4 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                        <Button :disabled="form.processing">Save Contact Details</Button>

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
