<script setup lang="ts">
import UserDrawer from '@/components/users/UserDrawer.vue';
import UsersDataTable from '@/components/users/UsersDataTable.vue';
import UsersFilters from '@/components/users/UsersFilters.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { toIndexQuery } from '@/lib/usersQuery';
import type {
    PaginatedUsers,
    UserRow,
    UsersIndexFilters,
} from '@/types/users';
import { index as usersIndexRoute } from '@/routes/users';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    users: PaginatedUsers;
    filters: UsersIndexFilters;
    can: { create: boolean };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: usersIndexRoute.url(),
            },
        ],
    },
});

const filtersState = ref<UsersIndexFilters>({ ...props.filters });

watch(
    () => props.filters,
    (f) => {
        filtersState.value = { ...f };
    },
    { deep: true },
);

const drawerOpen = ref(false);
const drawerMode = ref<'create' | 'edit'>('create');
const selectedUser = ref<UserRow | null>(null);

const preserveQuery = computed(() => toIndexQuery(filtersState.value));

function visitIndex(overrides: Partial<{ page: number }> = {}): void {
    const q = toIndexQuery(filtersState.value, overrides);
    router.get(usersIndexRoute.url(q), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function onApplyFilters(): void {
    visitIndex({ page: 1 });
}

function onResetFilters(): void {
    filtersState.value = {
        search: '',
        verified: 'all',
        created_from: null,
        created_to: null,
        sort: 'created_at',
        direction: 'desc',
        per_page: 15,
    };
    visitIndex({ page: 1 });
}

function onSortChange(column: UsersIndexFilters['sort']): void {
    if (filtersState.value.sort === column) {
        filtersState.value.direction =
            filtersState.value.direction === 'asc' ? 'desc' : 'asc';
    } else {
        filtersState.value.sort = column;
        filtersState.value.direction = 'asc';
    }
    visitIndex({ page: 1 });
}

function openCreate(): void {
    drawerMode.value = 'create';
    selectedUser.value = null;
    drawerOpen.value = true;
}

function openEdit(user: UserRow): void {
    drawerMode.value = 'edit';
    selectedUser.value = user;
    drawerOpen.value = true;
}

function onDrawerSaved(): void {
    drawerOpen.value = false;
}

const pageInfo = computed(() => {
    const { current_page: current, last_page: last, total } = props.users;
    return `Page ${current} of ${last} (${total} total)`;
});
</script>

<template>
    <Head title="Users" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <Heading
                variant="small"
                title="Users"
                description="Manage accounts, verification, and access."
            />
            <Button
                v-if="can.create"
                type="button"
                class="shrink-0"
                @click="openCreate"
            >
                New user
            </Button>
        </div>

        <UsersFilters
            v-model="filtersState"
            @apply="onApplyFilters"
            @reset="onResetFilters"
        />

        <UsersDataTable
            :data="users.data"
            :sort="filtersState.sort"
            :direction="filtersState.direction"
            @select="openEdit"
            @sort-change="onSortChange"
        />

        <div
            class="text-muted-foreground flex flex-col gap-3 text-sm sm:flex-row sm:items-center sm:justify-between"
        >
            <span>{{ pageInfo }}</span>
            <div class="flex flex-wrap gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="users.current_page <= 1"
                    @click="visitIndex({ page: users.current_page - 1 })"
                >
                    Previous
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="users.current_page >= users.last_page"
                    @click="visitIndex({ page: users.current_page + 1 })"
                >
                    Next
                </Button>
            </div>
        </div>
    </div>

    <UserDrawer
        v-model:open="drawerOpen"
        :mode="drawerMode"
        :user="selectedUser"
        :preserve-query="preserveQuery"
        @saved="onDrawerSaved"
    />
</template>
