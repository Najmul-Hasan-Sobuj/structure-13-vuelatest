export type UserRow = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string | null;
    can: {
        update: boolean;
        delete: boolean;
    };
};

export type UsersIndexFilters = {
    search: string;
    verified: 'all' | 'yes' | 'no';
    created_from: string | null;
    created_to: string | null;
    sort: 'created_at' | 'name' | 'email';
    direction: 'asc' | 'desc';
    per_page: number;
};

/** Laravel paginator JSON shape from `LengthAwarePaginator`. */
export type PaginatedUsers = {
    data: UserRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
};
