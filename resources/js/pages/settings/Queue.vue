<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { edit } from '@/routes/queue-monitor';
import { RefreshCw } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    connectionName: string;
    driver: string;
    pendingSize: number;
    failedCount: number | null;
    reservedCount: number | null;
    batchSummary: {
        active_batches: number;
        pending_jobs: number;
        failed_jobs: number;
    } | null;
}>();

const reloadKeys = [
    'connectionName',
    'driver',
    'pendingSize',
    'failedCount',
    'reservedCount',
    'batchSummary',
] as const;

function refresh() {
    router.reload({ only: [...reloadKeys] });
}

const driverNote = computed(() => {
    if (props.driver === 'sync') {
        return 'Jobs are executed synchronously during the request, so there is no queue backlog.';
    }
    if (props.driver === 'redis') {
        return 'For deep Redis queue monitoring (throughput, workers, dashboards), consider Laravel Horizon.';
    }
    return null;
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Queue',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Queue" />

    <h1 class="sr-only">Queue monitor</h1>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <Heading
                variant="small"
                title="Queue"
                description="Monitor the default queue connection for this application"
            />
            <Button
                type="button"
                variant="outline"
                size="sm"
                class="shrink-0 self-start"
                @click="refresh"
            >
                <RefreshCw class="mr-2 h-4 w-4" />
                Refresh
            </Button>
        </div>

        <p
            v-if="driverNote"
            class="text-sm text-muted-foreground"
        >
            {{ driverNote }}
        </p>

        <div class="grid gap-4 sm:grid-cols-2">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-base">Connection</CardTitle>
                    <CardDescription> Default queue connection name </CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="font-mono text-sm">{{ connectionName }}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-base">Driver</CardTitle>
                    <CardDescription> Queue backend driver </CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="font-mono text-sm">{{ driver }}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-base">Pending jobs</CardTitle>
                    <CardDescription> Jobs waiting on the default queue name </CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold tabular-nums">
                        {{ pendingSize }}
                    </p>
                </CardContent>
            </Card>

            <Card v-if="reservedCount !== null">
                <CardHeader class="pb-2">
                    <CardTitle class="text-base">In progress</CardTitle>
                    <CardDescription> Database jobs currently reserved by a worker </CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold tabular-nums">
                        {{ reservedCount }}
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-base">Failed jobs</CardTitle>
                    <CardDescription> Total recorded failures </CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold tabular-nums">
                        {{ failedCount === null ? 'N/A' : failedCount }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <Card v-if="batchSummary">
            <CardHeader class="pb-2">
                <CardTitle class="text-base">Active batches</CardTitle>
                <CardDescription>
                    {{ batchSummary.active_batches }} unfinished
                    {{ batchSummary.active_batches === 1 ? 'batch' : 'batches' }}
                </CardDescription>
            </CardHeader>
            <CardContent class="flex flex-wrap gap-6 text-sm">
                <div>
                    <span class="text-muted-foreground">Pending in batches</span>
                    <p class="text-lg font-semibold tabular-nums">
                        {{ batchSummary.pending_jobs }}
                    </p>
                </div>
                <div>
                    <span class="text-muted-foreground">Failed in batches</span>
                    <p class="text-lg font-semibold tabular-nums">
                        {{ batchSummary.failed_jobs }}
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
