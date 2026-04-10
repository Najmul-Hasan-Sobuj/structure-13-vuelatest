import type { UsersIndexFilters } from '@/types/users';

export type UsersIndexRouteQuery = {
    query: Record<string, string | number>;
};

/**
 * Build Wayfinder query options from index filters (omit empty / default values).
 */
export function toIndexQuery(
    filters: UsersIndexFilters,
    overrides: Partial<
        Pick<UsersIndexFilters, 'sort' | 'direction' | 'per_page'> & {
            page: number | undefined;
        }
    > = {},
): UsersIndexRouteQuery {
    const f = { ...filters, ...overrides };
    const query: Record<string, string | number> = {};

    if (f.search) {
        query.search = f.search;
    }
    if (f.verified !== 'all') {
        query.verified = f.verified;
    }
    if (f.created_from) {
        query.created_from = f.created_from;
    }
    if (f.created_to) {
        query.created_to = f.created_to;
    }
    query.sort = f.sort;
    query.direction = f.direction;
    query.per_page = f.per_page;
    if (overrides.page !== undefined && overrides.page > 1) {
        query.page = overrides.page;
    }

    return { query };
}
