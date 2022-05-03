import {Tag} from "./tag";
import {User} from "./user";
import {Language} from "./language";

export type Post = {

    id: number;
    preview_id: string;
    created_at: number;
    updated_at: number;
    published_at: number | null;

    is_featured: boolean;
    is_page: boolean;

    slug: string;

    featured_image_url: string | null;
    canonical_url: string | null;
    code_head: string | null;
    code_foot: string | null;

    variants: {[key: number]: PostVariant};

    tags: Tag[];
    authors: User[];

};

export type PostVariant = {

    language_id: number;

    status: PostStatus,

    content: string | null;
    content_unsaved: string | null;
    title: string | null;
    description: string | null;

};

export enum PostStatus {
    DRAFT = 'draft',
    PUBLISHED = 'published',
    SCHEDULED = 'scheduled'
}