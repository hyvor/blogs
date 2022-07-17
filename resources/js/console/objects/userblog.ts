import {BlogType, UserRole} from "../enums";
import {Subscription} from "../types";

export type UserBlog = {
    user: UserBlogUser;
    blog: UserBlogBlog
};

export type UserBlogBlog = {
    id: number;
    subdomain: string;
    name: string;
    type: BlogType,
    base_url: string;
    logo_url: string | null;

    posts_count: number;
    users_count: number;

    is_on_trial: boolean;
    trial_ends_at: number | null;
    subscription: Subscription;

};

export type UserBlogUser = {

    id: number;
    role: UserRole;
    posts_count: number;

};