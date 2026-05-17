<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <div
        :class="className"
        v-bind="$attrs"
        class="flex items-center justify-center font-bold text-xl"
    >
        NH
    </div>
</template>
