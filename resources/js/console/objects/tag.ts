
export type Tag = {

    id: number;
    created_at: number;
    updated_at: number;

    slug: string;

    posts_count: number;
    code_head: number;
    code_foot: number;

    variants: {[key: number]: TagVariant}

}

export type TagVariant = {

    language_id: number;
    url: string;
    name: string | null;
    description: string | null;

}