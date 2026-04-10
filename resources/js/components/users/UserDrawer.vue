<script setup lang="ts">
import UserController from '@/actions/App/Http/Controllers/Users/UserController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import type { UsersIndexRouteQuery } from '@/lib/usersQuery';
import type { UserRow } from '@/types/users';
import { Form, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    open: boolean;
    mode: 'create' | 'edit';
    user: UserRow | null;
    preserveQuery: UsersIndexRouteQuery;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const deleteOpen = ref(false);

watch(
    () => props.open,
    (v) => {
        if (!v) {
            deleteOpen.value = false;
        }
    },
);

function close(): void {
    emit('update:open', false);
}

function isoToDatetimeLocal(iso: string | null): string {
    if (!iso) {
        return '';
    }
    const d = new Date(iso);
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

const verifiedDefault = computed(() =>
    props.user ? isoToDatetimeLocal(props.user.email_verified_at) : '',
);

const createForm = computed(() =>
    // Wayfinder Vite plugin injects `.form` at build time.
    (UserController.store as typeof UserController.store & { form: { post: () => object } }).form.post(),
);

const updateForm = computed(() => {
    if (!props.user) {
        return { action: '', method: 'post' as const };
    }
    return (
        UserController.update as typeof UserController.update & {
            form: {
                patch: (
                    args: { user: number },
                    q?: UsersIndexRouteQuery,
                ) => object;
            };
        }
    ).form.patch({ user: props.user.id }, props.preserveQuery);
});

function onSaved(): void {
    emit('saved');
    close();
}

function runDelete(): void {
    if (!props.user) {
        return;
    }
    router.delete(
        UserController.destroy.url({ user: props.user.id }, props.preserveQuery),
        {
            preserveScroll: true,
            onSuccess: () => {
                deleteOpen.value = false;
                onSaved();
            },
        },
    );
}
</script>

<template>
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent side="bottom" class="max-h-[90vh] overflow-y-auto">
            <SheetHeader class="text-left">
                <SheetTitle>
                    {{
                        mode === 'create' ? 'Create user' : 'Edit user'
                    }}
                </SheetTitle>
                <SheetDescription>
                    {{
                        mode === 'create'
                            ? 'Add a new account. The user can sign in with this email and password.'
                            : 'Update profile, verification, or password.'
                    }}
                </SheetDescription>
            </SheetHeader>

            <div class="mt-6 pb-8">
                <Form
                    v-if="mode === 'create'"
                    v-bind="createForm"
                    class="space-y-4"
                    reset-on-success
                    @success="onSaved"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="create-name">Name</Label>
                        <Input
                            id="create-name"
                            name="name"
                            type="text"
                            required
                            autocomplete="name"
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="create-email">Email</Label>
                        <Input
                            id="create-email"
                            name="email"
                            type="email"
                            required
                            autocomplete="off"
                        />
                        <InputError :message="errors.email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="create-password">Password</Label>
                        <PasswordInput
                            id="create-password"
                            name="password"
                            required
                            autocomplete="new-password"
                        />
                        <InputError :message="errors.password" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="create-password-confirmation"
                            >Confirm password</Label
                        >
                        <PasswordInput
                            id="create-password-confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="create-verified-at"
                            >Email verified at (optional)</Label
                        >
                        <Input
                            id="create-verified-at"
                            name="email_verified_at"
                            type="datetime-local"
                        />
                        <InputError :message="errors.email_verified_at" />
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <Button type="submit" :disabled="processing">
                            Create user
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="close"
                        >
                            Cancel
                        </Button>
                    </div>
                </Form>

                <Form
                    v-else-if="mode === 'edit' && user"
                    :key="user.id"
                    v-bind="updateForm"
                    class="space-y-4"
                    reset-on-success
                    @success="onSaved"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="edit-name">Name</Label>
                        <Input
                            id="edit-name"
                            name="name"
                            type="text"
                            required
                            autocomplete="name"
                            :default-value="user.name"
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-email">Email</Label>
                        <Input
                            id="edit-email"
                            name="email"
                            type="email"
                            required
                            autocomplete="off"
                            :default-value="user.email"
                        />
                        <InputError :message="errors.email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-password"
                            >New password (optional)</Label
                        >
                        <PasswordInput
                            id="edit-password"
                            name="password"
                            autocomplete="new-password"
                        />
                        <InputError :message="errors.password" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-password-confirmation"
                            >Confirm new password</Label
                        >
                        <PasswordInput
                            id="edit-password-confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-verified-at"
                            >Email verified at</Label
                        >
                        <Input
                            id="edit-verified-at"
                            name="email_verified_at"
                            type="datetime-local"
                            :default-value="verifiedDefault"
                        />
                        <InputError :message="errors.email_verified_at" />
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <Button type="submit" :disabled="processing">
                            Save changes
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="close"
                        >
                            Cancel
                        </Button>
                        <Button
                            v-if="user.can.delete"
                            type="button"
                            variant="destructive"
                            class="ms-auto"
                            @click="deleteOpen = true"
                        >
                            Delete user
                        </Button>
                    </div>
                </Form>
            </div>
        </SheetContent>
    </Sheet>

    <Dialog v-model:open="deleteOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete this user?</DialogTitle>
                <DialogDescription>
                    This permanently removes
                    {{ user?.email ?? 'this account' }}. This cannot be
                    undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2 sm:gap-0">
                <Button
                    type="button"
                    variant="outline"
                    @click="deleteOpen = false"
                >
                    Cancel
                </Button>
                <Button type="button" variant="destructive" @click="runDelete">
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
