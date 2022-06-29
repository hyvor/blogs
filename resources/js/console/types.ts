import {
    BlogHostingAt,
    BlogType,
    ColorModeDefault,
    ColorModes,
    CommentsType,
    SeoExternalLinksFollow, UserRole,
    UserStatus
} from "./enums";
import {UserBlog} from "./objects/userblog";

// === CONSOLE

export type ConsoleWindow = (typeof window) & {
    appConfig: appConfig,
    currentSubdomain?: string
}

export interface appConfig {

    hyvorUser: any,
    blogs: UserBlog[],

    domains: {
        app: string,
        delivery: string,
        hyvor: string
    },

    syntax_themes: string[],

    limits: {
        max_theme_zip_size_kb: number
    }
}

export interface Filters {
    status: string,
    author: string | number | null,
    tag: string | number | null,
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

    variants: BlogVariant[]

}

export interface PostCounts {
    published: number,
    draft: number,
    scheduled: number,
    featured: number
}

// == POST
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

    variants: PostVariant[];

    tags: Tag[];
    authors: User[];

};

export type PostStatus = 'draft' | 'published' | 'scheduled'

export type PostVariant = {

    language_id: number;

    status: PostStatus,
    url: string,

    content: string | null;
    content_unsaved: string | null;
    title: string | null;
    description: string | null;

};

// == USER
export type User = {
    id: number;
    created_at: number;
    updated_at: number;

    hyvor_user_id: number | null;

    status: UserStatus;
    role: UserRole;
    slug: string;
    posts_count: number;
    email: string;

    picture_url: string | null;
    website_url: string | null;

    social_facebook: string | null;
    social_twitter: string | null;
    social_linkedin: string | null;
    social_youtube: string | null;
    social_tiktok: string | null;
    social_instagram: string | null;
    social_github: string | null;

    variants: UserVariant[]
};

export type UserVariant = {
    language_id: number;

    url: string;

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
    code_head: string | null;
    code_foot: string | null;

    variants: TagVariant[]

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
    name: string;
    url: string;
    original_name: string;
    extension: string;

}

export interface UnsplashImage {
    url: string;
    author: string;
    authorUrl: string;
    title: string | null;
    alt: string | null;
}

// === REDIRECT

export interface Redirect {
    id: number;
    created_at: number;
    path: string;
    to: string;
    type: 'temporary' | 'permanent'
}

// === ROUTES

export interface Route {
    id: number;
    created_at: number;
    name: string;
    match: string;
    template: string;
    posts_filter: string | null;
    content_type: string | null;
    is_enabled: boolean
}

// === NAVIGATION
export interface Navigation {
    id: number;
    created_at: number;
    url: string;
    type: NavigationType,
    sort: number;
    variants: NavigationVariant[]
}

export type NavigationType = 'header' | 'footer';

export interface NavigationVariant {
    navigation_id: number,
    language_id: number,
    name: string
}

// API

export interface ApiKey {
    id: number,
    name: string,
    type: ApiKeyType,
    api_key: string
}

export type ApiKeyType = 'console' | 'delivery';

// === THEME

export interface Theme {
    id: number,
    type: 'original' | 'ported',
    name: string
}

export type ThemeFolder = 'templates' | 'assets' | 'styles' | 'lang' | null

export interface ThemeFile {
    id: number,
    name: string,
    content: string | null,
    folder: ThemeFolder
}