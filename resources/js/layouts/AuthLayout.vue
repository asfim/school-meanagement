<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import FlashToast from '@/components/FlashToast.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <FlashToast />
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
