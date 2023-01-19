import {BlogType, UserRole} from "../enums";
import {Subscription} from "../types";

export type UserBlog = {
    user: UserBlogUser;
    blog: UserBlogBlog
};

export type UserBlogBlog = {
    id: number;
    is_blocked: boolean,
    subdomain: string;
    name: string;
    type: BlogType,
    billing_type: 'paddle' | 'shopify',
    integration: 'shopify' | null,
    base_url: string;
    logo_url: string | null;

    posts_count: number;
    users_count: number;

    // trial_ends_at: number;
    subscription: Subscription | null;

};

export type UserBlogUser = {

    id: number;
    role: UserRole;
    posts_count: number;

};