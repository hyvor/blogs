import {BlogType} from "../enums";
import {Subscription} from "./subscription";

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
    subscribed: boolean;
    subscription: Subscription;

    default_language: any;
};

export type UserBlogUser = {

    id: number;
    role: string;

};