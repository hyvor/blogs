import {BlogType} from "../enums/BlogType";

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
    trial_ends_at: boolean | null;
    subscribed: boolean;

    subscription: any;
    default_language: any;
};

export type UserBlogUser = {

    id: number;
    role: string;

};