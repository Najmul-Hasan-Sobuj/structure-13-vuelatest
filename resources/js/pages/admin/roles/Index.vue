<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle as DialogTitleCmp,
    DialogDescription as DialogDescriptionCmp,
    DialogFooter,
} from '@/components/ui/dialog';
import { ref } from 'vue';
import { usePermissions } from '@/composables/usePermissions';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Roles', href: '/admin/roles' },
        ],
    },
});

const props = defineProps<{
    roles: Array<any>;
    permissionGroups: Record<string, Array<any>>;
}>();

const { can } = usePermissions();

const isSheetOpen = ref(false);
const isEditing = ref(false);
const editingRoleId = ref<number | null>(null);
const isDeleteDialogOpen = ref(false);
const deleteTarget = ref<{ id: number | null; name?: string }>({ id: null });

const form = useForm({
    name: '',
    permissions: [] as string[],
});

const openCreateSheet = () => {
    isEditing.value = false;
    editingRoleId.value = null;
    
    form.name = '';
    form.permissions = [];
    
    form.defaults({
        name: '',
        permissions: []
    });
    form.clearErrors();
    isSheetOpen.value = true;
};

const openEditSheet = (role: any) => {
    isEditing.value = true;
    editingRoleId.value = role.id;
    
    // Set the data directly instead of just defaults
    form.name = role.name;
    form.permissions = role.permissions ? role.permissions.map((p: any) => p.name) : [];
    
    form.defaults({
        name: form.name,
        permissions: [...form.permissions]
    });
    form.clearErrors();
    isSheetOpen.value = true;
};

const togglePermission = (permName: string) => {
    const index = form.permissions.indexOf(permName);
    if (index === -1) {
        form.permissions.push(permName);
    } else {
        form.permissions.splice(index, 1);
    }
};

const submit = () => {
    if (isEditing.value && editingRoleId.value) {
        form.put(`/admin/roles/${editingRoleId.value}`, {
            onSuccess: () => { isSheetOpen.value = false; },
        });
    } else {
        form.post('/admin/roles', {
            onSuccess: () => { isSheetOpen.value = false; },
        });
    }
};

const openDelete = (role: any) => {
    deleteTarget.value = { id: role.id, name: role.name };
    isDeleteDialogOpen.value = true;
};

const confirmDelete = () => {
    if (!deleteTarget.value.id) return;

    router.delete(`/admin/roles/${deleteTarget.value.id}`, {
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
        },
    });
};
</script>

<template>
    <Head title="Roles" />
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between space-y-2 px-2 pb-2">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">Roles</h1>
                <p class="text-sm text-muted-foreground">Manage admin roles and permissions.</p>
            </div>
            <Button v-if="can('create roles')" @click="openCreateSheet">Create Role</Button>
        </div>

        <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card text-card-foreground p-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="py-3 px-4 font-medium">Name</th>
                        <th class="py-3 px-4 font-medium">Permissions</th>
                        <th class="py-3 px-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="role in roles" :key="role.id" class="border-b last:border-0 hover:bg-muted/50 transition-colors">
                        <td class="py-3 px-4 font-medium">{{ role.name }}</td>
                        <td class="py-3 px-4">
                            <div class="flex flex-wrap gap-1">
                                <span v-for="perm in role.permissions" :key="perm.id" class="inline-flex items-center rounded-md bg-secondary px-2 py-1 text-xs font-medium text-secondary-foreground">
                                    {{ perm.name }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3 px-4 flex justify-end gap-2">
                            <Button v-if="can('edit roles')" variant="outline" size="sm" @click="openEditSheet(role)">Edit</Button>
                            <Button v-if="can('delete roles')" variant="destructive" size="sm" @click="openDelete(role)">Delete</Button>
                        </td>
                    </tr>
                    <tr v-if="roles.length === 0">
                        <td colspan="3" class="text-center py-6 text-muted-foreground">No roles found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Sheet v-model:open="isSheetOpen">
            <SheetContent side="bottom" class="max-h-[90vh] rounded-t-[2rem] p-0 flex flex-col">
                <div class="w-full max-w-3xl mx-auto flex-1 overflow-y-auto px-6 py-4">
                    <SheetHeader class="mb-6 text-left">
                        <SheetTitle>{{ isEditing ? 'Edit Role' : 'Create Role' }}</SheetTitle>
                    <SheetDescription>
                        {{ isEditing ? 'Update the role name and adjust permissions below.' : 'Add a new role and configure its permissions.' }}
                    </SheetDescription>
                </SheetHeader>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="name">Role Name</Label>
                        <Input id="name" v-model="form.name" type="text" placeholder="e.g. Editor" required />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-4">
                        <Label class="text-base font-medium">Permissions</Label>
                        <p v-if="form.errors.permissions" class="text-sm text-destructive">{{ form.errors.permissions }}</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="(perms, group) in permissionGroups" :key="group" class="space-y-3 p-4 border rounded-lg bg-card">
                                <h3 class="font-medium text-primary text-sm uppercase tracking-wider">{{ group || 'Uncategorized' }}</h3>
                                <div class="flex flex-col gap-2">
                                    <div v-for="perm in perms" :key="perm.id" class="flex items-start space-x-2 cursor-pointer" @click="togglePermission(perm.name)">
                                        <Checkbox 
                                            :id="`perm-${perm.id}`" 
                                            :model-value="form.permissions.includes(perm.name)"
                                            class="mt-1 pointer-events-none"
                                        />
                                        <Label :for="`perm-${perm.id}`" class="font-normal text-sm leading-snug cursor-pointer pointer-events-none">{{ perm.name }}</Label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-6">
                        <Button variant="outline" type="button" @click="isSheetOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ isEditing ? 'Update Role' : 'Save Role' }}
                        </Button>
                    </div>
                </form>
                </div>
            </SheetContent>
        </Sheet>

        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitleCmp>Delete Role</DialogTitleCmp>
                    <DialogDescriptionCmp>
                        Are you sure you want to delete the role "{{ deleteTarget.name }}"? This action cannot be undone.
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
