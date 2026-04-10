<script setup lang="ts">
import DateRangePicker from '@/components/users/DateRangePicker.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    NativeSelect,
    NativeSelectOption,
} from '@/components/ui/native-select';
import type { UsersIndexFilters } from '@/types/users';
import { reactive, watch } from 'vue';

const props = defineProps<{
    modelValue: UsersIndexFilters;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: UsersIndexFilters];
    apply: [];
    reset: [];
}>();

const draft = reactive<UsersIndexFilters>({ ...props.modelValue });

watch(
    () => props.modelValue,
    (v) => {
        Object.assign(draft, v);
    },
    { deep: true },
);

function onApply(): void {
    emit('update:modelValue', { ...draft });
    emit('apply');
}

function onReset(): void {
    emit('reset');
}
</script>

<template>
    <div
        class="border-border flex flex-col gap-4 rounded-lg border p-4 md:flex-row md:flex-wrap md:items-end"
    >
        <div class="grid min-w-[12rem] flex-1 gap-2">
            <Label for="users-search">Search</Label>
            <Input
                id="users-search"
                v-model="draft.search"
                type="search"
                placeholder="Name or email"
                autocomplete="off"
            />
        </div>
        <div class="grid w-full gap-2 md:w-44">
            <Label for="users-verified">Email verified</Label>
            <NativeSelect id="users-verified" v-model="draft.verified">
                <NativeSelectOption value="all">All</NativeSelectOption>
                <NativeSelectOption value="yes">Verified</NativeSelectOption>
                <NativeSelectOption value="no">Not verified</NativeSelectOption>
            </NativeSelect>
        </div>
        <div class="grid w-full gap-2 md:w-36">
            <Label for="users-per-page">Per page</Label>
            <NativeSelect
                id="users-per-page"
                :model-value="String(draft.per_page)"
                @update:model-value="
                    draft.per_page = Number($event) as 10 | 15 | 25 | 50
                "
            >
                <NativeSelectOption value="10">10</NativeSelectOption>
                <NativeSelectOption value="15">15</NativeSelectOption>
                <NativeSelectOption value="25">25</NativeSelectOption>
                <NativeSelectOption value="50">50</NativeSelectOption>
            </NativeSelect>
        </div>
        <div class="grid gap-2">
            <span class="text-sm font-medium">Created</span>
            <DateRangePicker
                :from="draft.created_from"
                :to="draft.created_to"
                @update:from="draft.created_from = $event"
                @update:to="draft.created_to = $event"
            />
        </div>
        <div class="flex flex-wrap gap-2 md:pb-0.5">
            <Button type="button" @click="onApply">Apply filters</Button>
            <Button type="button" variant="outline" @click="onReset">
                Reset
            </Button>
        </div>
    </div>
</template>
