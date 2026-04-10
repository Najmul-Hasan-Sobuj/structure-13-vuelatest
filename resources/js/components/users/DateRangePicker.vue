<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { CalendarRange } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    from: string | null;
    to: string | null;
}>();

const emit = defineEmits<{
    'update:from': [value: string | null];
    'update:to': [value: string | null];
}>();

const open = ref(false);
const draftFrom = ref(props.from ?? '');
const draftTo = ref(props.to ?? '');

watch(
    () => [props.from, props.to],
    () => {
        draftFrom.value = props.from ?? '';
        draftTo.value = props.to ?? '';
    },
);

const summary = computed(() => {
    if (props.from && props.to) {
        return `${props.from} → ${props.to}`;
    }
    if (props.from) {
        return `From ${props.from}`;
    }
    if (props.to) {
        return `Until ${props.to}`;
    }
    return 'Created date range';
});

function apply(): void {
    emit('update:from', draftFrom.value || null);
    emit('update:to', draftTo.value || null);
    open.value = false;
}

function clearRange(): void {
    draftFrom.value = '';
    draftTo.value = '';
    emit('update:from', null);
    emit('update:to', null);
    open.value = false;
}
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                type="button"
                variant="outline"
                class="min-w-[12rem] justify-start gap-2 font-normal"
            >
                <CalendarRange class="size-4 opacity-70" />
                <span class="truncate">{{ summary }}</span>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-4" align="start">
            <div class="grid gap-3">
                <p class="text-sm font-medium">Filter by created date</p>
                <div class="grid gap-2">
                    <Label for="users-daterange-from" class="text-xs"
                        >From</Label
                    >
                    <input
                        id="users-daterange-from"
                        v-model="draftFrom"
                        type="date"
                        class="border-input bg-background ring-offset-background focus-visible:ring-ring flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="users-daterange-to" class="text-xs"
                        >To</Label
                    >
                    <input
                        id="users-daterange-to"
                        v-model="draftTo"
                        type="date"
                        class="border-input bg-background ring-offset-background focus-visible:ring-ring flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                    />
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    <Button type="button" size="sm" @click="apply">
                        Apply range
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        variant="ghost"
                        @click="clearRange"
                    >
                        Clear
                    </Button>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
