<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();

const page = usePage();
const siteSettings = computed(() => page.props.site_settings as {
    institute_name: string;
    tagline: string;
    logo_path: string | null;
    favicon_path: string | null;
});
</script>

<template>
    <div class="flex-1 min-w-0 text-left text-sm py-1">
        <span class="font-bold text-neutral-100 dark:text-neutral-55 truncate block leading-tight group-data-[collapsible=icon]:hidden">
            {{ siteSettings.institute_name }}
        </span>
        <span class="font-bold text-neutral-950 dark:text-neutral-55 hidden group-data-[collapsible=icon]:block text-center text-lg">
            {{ siteSettings.institute_name.charAt(0) }}
        </span>
    </div>
</template>
