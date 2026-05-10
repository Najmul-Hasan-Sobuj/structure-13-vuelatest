<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineOptions({
    layout: {
        title: 'Admin dashboard',
        description: 'Manage admin-only application settings and access.',
    },
});
</script>

<template>
    <Head title="Admin dashboard" />

    <section class="space-y-6">
        <div class="space-y-2">
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-muted-foreground">
                Admin area
            </p>
            <h1 class="text-3xl font-semibold tracking-tight">
                Admin dashboard
            </h1>
            <p class="max-w-2xl text-sm text-muted-foreground">
                This is the protected entry point for admin-only functionality. We will expand this area with admin tools, permissions, and management screens.
            </p>
        </div>
    </section>
</template>