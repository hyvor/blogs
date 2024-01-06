import { WebhookEventNames } from "../[subdomain]/settings/webhooks/webhookActions";

export type UserRole = 'owner' | 'editor' | 'writer' | 'contributor' | 'finance';

export type BlogType = 'default' | 'dev';

export interface AuthUser {
    id: number,
    name: string,
    username: string | null,
    picture_url: string | null
}

export interface BlogList {

    id: number,
    role: UserRole,
    is_blocked: boolean,
    trial_ends_at: number,
    name: string,
    subdomain: string,
    type: BlogType,
    url: string,
    logo_url: string | null,
    posts_count: number,
    users_count: number,

    subscription: Subscription | null,
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
    created_at: number,
    trial_ends_at: number,

    is_blocked: boolean,
    subdomain: string,
    type: BlogType,
    hosting_at: 'subdomain' | 'domain' | 'self',
    hosting_domain: string | null,
    hosting_url: string | null,

    url: string,

    embeddable: boolean,
    embedding_domains: string | null,

    logo_url: string | null,
    cover_url: string | null,
    icon_url: string | null,

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
    seo_external_links_follow: 'follow' | 'nofollow',
    comments_code: string | null,
    newsletter_code: string | null,

    color_modes: 'light' | 'dark' | 'both',
    color_mode_default: 'light' | 'dark' | 'os',

    syntax_on: boolean,
    syntax_line_numbers: boolean,
    syntax_theme: string | null,
    heading_anchors: boolean,

    flashload: boolean,
    variants: BlogVariant[],

    link_analysis_enabled: boolean,
    link_analysis_email_report: 'always' | 'broken' | 'never',

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

    // slug: string;

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

    id: number,
    language_id: number;
    slug: string | null,
    status: PostStatus,
    url: string,

    content: string | null;
    content_unsaved: string | null;
    title: string | null;
    description: string | null;

    seo_primary_keyword: string | null,
    seo_secondary_keywords: string[],

    link_analysis: Record<string, number>,
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

    direction: 'ltr' | 'rtl'
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
    author_url: string;
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

// WEBHOOK

export type WebhookEvent = keyof typeof WebhookEventNames;


export interface Webhook {
    id: number,
    url: string,
    events: WebhookEvent[],
    secret: string
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

// === BILLING

export type SubscriptionPlan = 'starter' | 'growth' | 'premium' | 'team' | 'business' | 'enterprise';
export type SubscriptionFrequency = 'monthly' | 'yearly';

export interface Subscription {

    id: number,
    status: 'active' | 'past_due' | 'deleted',
    plan: SubscriptionPlan,
    frequency: SubscriptionFrequency,
    created_at: number,
    ends_at: number | null,

    paddle_subscription_id: number | null,
    shopify_subscription_id: string | null,

}


export interface Usage {

    current: number;
    total: number;
    percentage: number; // float

}

export interface UsageTypes {
    users: Usage,
    media: Usage,
    auto_translate: Usage,
    gpt: Usage
}

export interface PaddlePayment {

    id: number,
    paid_at: number,
    amount: number,
    currency: number,
    receipt_url: string

}

export interface PaddleSubscriptionInfo {

    email: string,

    card_brand: string,
    card_last_four: string | null,
    card_expiration: string | null,

    update_url: string,

    last_payment: PaddleSubscriptionInfoPayment,
    next_payment: PaddleSubscriptionInfoPayment | null,

}

export interface PaddleSubscriptionInfoPayment {
    amount: number,
    currency: string,
    at: number
}


export type JobStatus = 'pending' | 'completed' | 'failed';


export interface Export {
    id: number,
    created_at: number,
    format: 'hyvor_blogs' | 'wordpress',
    status: JobStatus,
    url: string | null
    error: string | null
}

export interface Import {

    id: number,
    created_at: number,
    name: string | null,
    type: 'sitemap' | 'wordpress',
    status: JobStatus,
    options: object,
    error: string | null,
    imported_counts: {
        posts: number,
        pages: number
        tags: number,
        users: number
    }

}

export interface LinkAnalysisLink {
    id: number,
    url: string,
    full_url: string,
    status_code: number,
    status_type: 'ok' | 'redirect' | 'broken' | 'ignored',
    ignored: boolean,

    post_id: number,
    post_variant_id: number,
    post_variant_language_id: number,
    post_variant_title: string | null,
}

export interface LinkAnalysisCheck {
    id: number,
    created_at: number,

    status: JobStatus,
    error: string | null,

    posts_count: number,
    post_variants_count: number,
    pages_count: number,
    page_variants_count: number,

    links_total_count: number,
    links_ok_count: number,
    links_broken_count: number,
    links_redirect_count: number,
    links_ignored_count: number,
}

export interface GptPrompt {
    id: number,
    created_at: number,
    post_id: number,

    prompt: string,
    gpt_response: string,
}