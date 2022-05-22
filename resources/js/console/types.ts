import {appConfig} from "./objects/appConfig";
import {
    BlogHostingAt,
    BlogType,
    ColorModeDefault,
    ColorModes,
    CommentsType,
    SeoExternalLinksFollow, UserRole,
    UserStatus
} from "./enums";

// === CONSOLE

export type ConsoleWindow = (typeof window) & {
    appConfig: appConfig,
    currentSubdomain?: string
}

export interface Filters {
    status: string,
    author: string | number,
    tag: number | null,
    startDate: Date | null,
    endDate: Date | null,
    search: string
}

// === BLOG

export interface BlogVariant {
    language_id: number;
    name: string | null;
    description: string | null;
}

export interface Blog {

    id: number,
    updated_at: number,
    subdomain: string,
    type: BlogType,
    hosting_at: BlogHostingAt,
    hosting_domain: string | null,
    hosting_url: string | null,

    logo_url: string | null,
    cover_url: string | null,

    social_facebook: string | null,
    social_twitter: string | null,
    social_linkedin: string | null,
    social_youtube: string | null,
    social_tiktok: string | null,
    social_instagram: string | null,
    social_github: string | null,

    code_head: string | null,
    code_foot: string | null,

    seo_indexing: boolean,
    seo_robots_txt: string | null,
    seo_external_links_follow: SeoExternalLinksFollow,

    comments_type: CommentsType,
    comments_ht_website_id: number | null,
    comments_ht_api_key: string | null,
    comments_code: string | null,

    newsletter_code: string | null,

    color_modes: ColorModes,
    color_mode_default: ColorModeDefault,

    syntax_on: boolean,
    syntax_line_numbers: boolean,
    syntax_theme: string | null

    variants: {
        [key: number]: BlogVariant
    }

}

export interface PostCounts {
    published: number,
    draft: number,
    scheduled: number,
    featured: number
}

// == USER
export type User = {
    id: number;
    created_at: number;
    updated_at: number;

    hyvor_user_id: number | null;

    status: UserStatus;
    role: UserRole;
    slug: string;
    email: string;

    picture_url: string | null;
    website_url: string | null;

    social_facebook: string | null;
    social_twitter: string | null;
    social_linkedin: string | null;
    social_youtube: string | null;
    social_instagram: string | null;
    social_github: string | null;

    variants: {
        [key: number]: UserVariant
    };
};

export type UserVariant = {
    language_id: number;

    name: string | null;
    bio: string | null;
    location: string | null;
}


// === TAG

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

// === LANGUAGE

export type Language = {
    id: number;
    code: string;
    name: string;
    is_primary: boolean;
}

// === MEDIA

export type Media = {

    id: number;
    uploaded_at: number;
    url: string;
    name: string;
    extension: string;

}