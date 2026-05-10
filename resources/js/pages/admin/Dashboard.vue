<script setup lang="ts">
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin Dashboard',
                href: '/admin',
            },
        ],
    },
});
</script>

<template>
    <Head title="Admin dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="space-y-2 px-2 pb-2">
            <p class="text-sm font-medium uppercase tracking-[0.3em] text-muted-foreground">Admin area</p>
            <h1 class="text-3xl font-semibold tracking-tight">Admin dashboard</h1>
            <p class="max-w-2xl text-sm text-muted-foreground">
                This is the protected entry point for admin-only functionality. We will expand this area with admin tools, permissions, and management screens.
            </p>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
        <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
            <PlaceholderPattern />
        </div>
    </div>
</template>