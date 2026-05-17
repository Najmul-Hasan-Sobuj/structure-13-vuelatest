<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { BookOpen, FolderGit2, LayoutGrid, ShieldCheck, Users } from 'lucide-vue-next';
import AdminNavUser from '@/components/AdminNavUser.vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import { usePermissions } from '@/composables/usePermissions';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin',
        icon: LayoutGrid,
    },
];

const { can } = usePermissions();

const authorizeNavItems = computed(() => {
    const items: NavItem[] = [];
    
    if (can('view roles')) {
        items.push({
            title: 'Roles',
            href: '/admin/roles',
            icon: ShieldCheck,
        });
    }
    
    if (can('view users')) {
        items.push({
            title: 'Users',
            href: '/admin/users',
            icon: Users,
        });
    }
    
    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/admin">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain title="Platform" :items="mainNavItems" />
            <NavMain v-if="authorizeNavItems.length > 0" title="Authorize" :items="authorizeNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <AdminNavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
