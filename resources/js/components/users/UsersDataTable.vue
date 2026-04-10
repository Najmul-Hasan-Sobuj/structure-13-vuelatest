<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { UserRow, UsersIndexFilters } from '@/types/users';
import {
    FlexRender,
    createColumnHelper,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { ArrowDown, ArrowUp } from 'lucide-vue-next';
import { computed, h } from 'vue';

const props = defineProps<{
    data: UserRow[];
    sort: UsersIndexFilters['sort'];
    direction: UsersIndexFilters['direction'];
}>();

const emit = defineEmits<{
    select: [user: UserRow];
    'sort-change': [sort: UsersIndexFilters['sort']];
}>();

const columnHelper = createColumnHelper<UserRow>();

function formatDate(iso: string | null): string {
    if (!iso) {
        return '—';
    }
    return new Date(iso).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

const columns = [
    columnHelper.accessor('name', {
        header: () => 'Name',
        cell: (ctx) => ctx.getValue(),
    }),
    columnHelper.accessor('email', {
        header: () => 'Email',
        cell: (ctx) => ctx.getValue(),
    }),
    columnHelper.accessor('email_verified_at', {
        header: () => 'Verified',
        cell: (ctx) =>
            ctx.getValue()
                ? h(Badge, { variant: 'secondary' }, { default: () => 'Yes' })
                : h(Badge, { variant: 'outline' }, { default: () => 'No' }),
    }),
    columnHelper.accessor('created_at', {
        header: () => 'Created',
        cell: (ctx) => formatDate(ctx.getValue()),
    }),
    columnHelper.display({
        id: 'actions',
        header: () => '',
        cell: (ctx) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    size: 'sm',
                    class: 'text-primary',
                    onClick: (e: MouseEvent) => {
                        e.stopPropagation();
                        emit('select', ctx.row.original);
                    },
                },
                () => 'Edit',
            ),
    }),
];

const table = useVueTable({
    get data() {
        return props.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
});

function isSortableColumn(id: string): id is UsersIndexFilters['sort'] {
    return id === 'name' || id === 'email' || id === 'created_at';
}

function toggleSort(columnId: string): void {
    if (!isSortableColumn(columnId)) {
        return;
    }
    emit('sort-change', columnId);
}

function sortIcon(columnId: string) {
    if (!isSortableColumn(columnId)) {
        return null;
    }
    if (props.sort !== columnId) {
        return null;
    }
    return props.direction === 'asc' ? ArrowUp : ArrowDown;
}

const headerGroups = computed(() => table.getHeaderGroups());
const rowModel = computed(() => table.getRowModel().rows);
</script>

<template>
    <div class="rounded-md border">
        <Table>
            <TableHeader>
                <TableRow
                    v-for="headerGroup in headerGroups"
                    :key="headerGroup.id"
                >
                    <TableHead
                        v-for="header in headerGroup.headers"
                        :key="header.id"
                        class="whitespace-nowrap"
                    >
                        <button
                            v-if="isSortableColumn(header.column.id)"
                            type="button"
                            class="hover:text-foreground inline-flex items-center gap-1 font-medium"
                            @click="toggleSort(header.column.id)"
                        >
                            <FlexRender
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                            <component
                                :is="sortIcon(header.column.id)"
                                v-if="sortIcon(header.column.id)"
                                class="size-3.5 opacity-70"
                            />
                        </button>
                        <template v-else>
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </template>
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="rowModel.length">
                    <TableRow
                        v-for="row in rowModel"
                        :key="row.id"
                        class="hover:bg-muted/50 cursor-pointer"
                        @click="emit('select', row.original)"
                    >
                        <TableCell
                            v-for="cell in row.getVisibleCells()"
                            :key="cell.id"
                        >
                            <FlexRender
                                :render="cell.column.columnDef.cell"
                                :props="cell.getContext()"
                            />
                        </TableCell>
                    </TableRow>
                </template>
                <TableRow v-else>
                    <TableCell
                        :colspan="columns.length"
                        class="text-muted-foreground h-24 text-center"
                    >
                        No users match these filters.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
