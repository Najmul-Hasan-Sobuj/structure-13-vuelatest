import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage<any>();

    const can = (permission: string | string[]) => {
        const permissions = page.props.auth.admin?.permissions || [];
        
        if (Array.isArray(permission)) {
            return permission.some(p => permissions.includes(p));
        }

        return permissions.includes(permission);
    };

    return { can };
}
