<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: '/admin/settings/appearance',
            },
        ],
    },
});
</script>

<template>
    <Head title="Admin appearance settings" />

    <h1 class="sr-only">Admin appearance settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Appearance settings"
            description="Update your account's appearance settings"
        />
        <AppearanceTabs />
    </div>
</template>
