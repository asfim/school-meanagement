<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import FlashToast from '@/components/FlashToast.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <FlashToast />
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
