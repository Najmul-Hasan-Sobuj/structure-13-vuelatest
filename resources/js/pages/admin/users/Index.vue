<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { Input as UiInput } from '@/components/ui/input';
import { ChevronDown } from 'lucide-vue-next';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from '@/components/ui/sheet';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle as DialogTitleCmp,
    DialogDescription as DialogDescriptionCmp,
    DialogFooter,
} from '@/components/ui/dialog';
import { ref, computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Users', href: '/admin/users' },
        ],
    },
});

const props = defineProps<{
    users: Array<any>;
    roles: Array<any>;
}>();

const { can } = usePermissions();

const isSheetOpen = ref(false);
const isEditing = ref(false);
const editingUserId = ref<number | null>(null);
const isRolesOpen = ref(true);
const isDeleteDialogOpen = ref(false);
const deleteTarget = ref<{ id: number | null; name?: string }>({ id: null });

const form = useForm({
    name: '',
    email: '',
    password: '',
    roles: [] as string[],
});

const searchRole = ref('');
const filteredRoles = computed(() => {
    const q = searchRole.value.trim().toLowerCase();
    if (!q) return props.roles;
    return props.roles.filter((r: any) => r.name.toLowerCase().includes(q));
});

const openCreateSheet = () => {
    isEditing.value = false;
    editingUserId.value = null;
    isRolesOpen.value = true;
    
    form.name = '';
    form.email = '';
    form.password = '';
    form.roles = [];

    form.defaults({
        name: '',
        email: '',
        password: '',
        roles: []
    });
    form.clearErrors();
    isSheetOpen.value = true;
};

const openEditSheet = (user: any) => {
    isEditing.value = true;
    editingUserId.value = user.id;
    isRolesOpen.value = true;

    form.name = user.name;
    form.email = user.email;
    form.password = ''; // Leave blank for edit unless they want to change it
    form.roles = user.roles ? user.roles.map((r: any) => r.name) : [];

    form.defaults({
        name: form.name,
        email: form.email,
        password: '',
        roles: [...form.roles]
    });
    form.clearErrors();
    isSheetOpen.value = true;
};

const toggleRole = (roleName: string) => {
    const index = form.roles.indexOf(roleName);
    if (index === -1) {
        form.roles.push(roleName);
    } else {
        form.roles.splice(index, 1);
    }
};

const submit = () => {
    if (isEditing.value && editingUserId.value) {
        form.put(`/admin/users/${editingUserId.value}`, {
            onSuccess: () => { isSheetOpen.value = false; },
        });
    } else {
        form.post('/admin/users', {
            onSuccess: () => { isSheetOpen.value = false; },
        });
    }
};

const openDelete = (user: any) => {
    deleteTarget.value = { id: user.id, name: user.name };
    isDeleteDialogOpen.value = true;
};

const confirmDelete = () => {
    if (!deleteTarget.value.id) return;

    router.delete(`/admin/users/${deleteTarget.value.id}`, {
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
        },
    });
};
</script>

<template>
    <Head title="Users" />
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between space-y-2 px-2 pb-2">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">Users</h1>
                <p class="text-sm text-muted-foreground">Manage admin accounts and their roles.</p>
            </div>
            <Button v-if="can('create users')" @click="openCreateSheet">Create User</Button>
        </div>

        <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card text-card-foreground p-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="py-3 px-4 font-medium">Name</th>
                        <th class="py-3 px-4 font-medium">Email</th>
                        <th class="py-3 px-4 font-medium">Roles</th>
                        <th class="py-3 px-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id" class="border-b last:border-0 hover:bg-muted/50 transition-colors">
                        <td class="py-3 px-4 font-medium">{{ user.name }}</td>
                        <td class="py-3 px-4 text-muted-foreground">{{ user.email }}</td>
                        <td class="py-3 px-4">
                            <div class="flex flex-wrap gap-1">
                                <span v-for="role in user.roles" :key="role.id" class="inline-flex items-center rounded-md bg-primary/10 text-primary px-2 py-1 text-xs font-medium">
                                    {{ role.name }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3 px-4 flex justify-end gap-2">
                            <Button v-if="can('edit users')" variant="outline" size="sm" @click="openEditSheet(user)">Edit</Button>
                            <Button v-if="can('delete users') && user.email !== 'admin@example.com'" variant="destructive" size="sm" @click="openDelete(user)">Delete</Button>
                        </td>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="4" class="text-center py-6 text-muted-foreground">No users found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Sheet v-model:open="isSheetOpen">
            <SheetContent side="bottom" class="max-h-[90vh] rounded-t-[2rem] p-0 flex flex-col">
                <div class="w-full max-w-2xl mx-auto flex-1 overflow-y-auto px-6 py-4">
                    <SheetHeader class="mb-6 text-left">
                        <SheetTitle>{{ isEditing ? 'Edit User' : 'Create User' }}</SheetTitle>
                    <SheetDescription>
                        {{ isEditing ? 'Update admin account details and roles.' : 'Add a new admin account and assign its roles.' }}
                    </SheetDescription>
                </SheetHeader>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" type="text" required />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" required />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="password">{{ isEditing ? 'Password (leave blank to keep current)' : 'Password' }}</Label>
                            <Input id="password" v-model="form.password" type="password" :required="!isEditing" />
                            <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                        </div>

                        <div class="space-y-3 pt-2">
                            <Label class="text-base font-medium">Roles</Label>
                            <p v-if="form.errors.roles" class="text-sm text-destructive">{{ form.errors.roles }}</p>

                            <Collapsible v-model:open="isRolesOpen" class="rounded-lg border bg-card overflow-hidden">
                                <CollapsibleTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        class="w-full justify-between rounded-none px-3 py-2.5 h-auto font-normal"
                                    >
                                        <span class="flex flex-wrap items-center gap-1 text-left">
                                            <span v-if="form.roles.length === 0" class="text-muted-foreground">Select roles...</span>
                                            <Badge v-for="role in form.roles" :key="role" variant="secondary">
                                                {{ role }}
                                            </Badge>
                                        </span>
                                        <ChevronDown class="size-4 shrink-0 transition-transform duration-200" :class="isRolesOpen ? 'rotate-180' : ''" />
                                    </Button>
                                </CollapsibleTrigger>

                                <CollapsibleContent>
                                    <div class="border-t bg-muted/20 p-2">
                                        <UiInput v-model="searchRole" placeholder="Search roles..." class="h-9 bg-background" />
                                    </div>
                                    <div class="max-h-[200px] overflow-y-auto p-3 flex flex-col gap-3">
                                        <div 
                                            v-for="role in filteredRoles" 
                                            :key="role.id" 
                                            class="flex items-center space-x-2"
                                        >
                                            <Checkbox 
                                                :id="`role-${role.id}`" 
                                                :model-value="form.roles.includes(role.name)"
                                                @update:model-value="(checked) => toggleRole(role.name)"
                                            />
                                            <Label :for="`role-${role.id}`" class="font-normal text-sm cursor-pointer">{{ role.name }}</Label>
                                        </div>
                                        <div v-if="filteredRoles.length === 0" class="text-sm text-muted-foreground text-center py-4">
                                            No roles found.
                                        </div>
                                    </div>
                                </CollapsibleContent>
                            </Collapsible>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-6">
                        <Button variant="outline" type="button" @click="isSheetOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ isEditing ? 'Update User' : 'Save User' }}
                        </Button>
                    </div>
                </form>
                </div>
            </SheetContent>
        </Sheet>

        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitleCmp>Delete User</DialogTitleCmp>
                    <DialogDescriptionCmp>
                        Are you sure you want to delete the user "{{ deleteTarget.name }}"? This action cannot be undone.
                    </DialogDescriptionCmp>
                </DialogHeader>

                <DialogFooter class="mt-4 flex justify-end gap-2">
                    <Button variant="outline" type="button" @click="isDeleteDialogOpen = false">Cancel</Button>
                    <Button variant="destructive" type="button" @click="confirmDelete">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
