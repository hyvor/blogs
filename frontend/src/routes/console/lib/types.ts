export interface License {
	users: number;
	storage: number;
	aiTokens: number;
	autoTranslationsChars: number;
	seoAnalysis: boolean;
	linkAnalysis: boolean;
	blogs: number;
}

export type UserRole = 'owner' | 'admin' | 'editor' | 'writer' | 'contributor';

export type BlogType = 'default' | 'dev' | 'temp';

export interface BlogList {
	id: number;
	role: UserRole;
	is_blocked: boolean;
	name: string;
	subdomain: string;
	type: BlogType;
	url: string;
	logo_url: string | null;
	posts_count: number;
	users_count: number;
}

export interface Filters {
	status: string;
	author: string | number | null;
	tag: string | number | null;
	startDate: Date | null;
	endDate: Date | null;
	search: string;
}

// === BLOG

export interface BlogVariant {
	language_id: number;
	name: string | null;
	description: string | null;
}

export interface Blog {
	id: number;
	created_at: number;

	is_blocked: boolean;
	subdomain: string;
	type: BlogType;
	hosting_at: 'subdomain' | 'domain' | 'self';
	hosting_domain: string | null;
	hosting_url: string | null;
	hosting_redirect_subdomain: boolean;

	url: string;

	embeddable: boolean;
	embedding_domains: string | null;

	logo_url: string | null;
	cover_url: string | null;
	icon_url: string | null;

	social_facebook: string | null;
	social_twitter: string | null;
	social_linkedin: string | null;
	social_youtube: string | null;
	social_tiktok: string | null;
	social_instagram: string | null;
	social_github: string | null;

	code_head: string | null;
	code_foot: string | null;

	seo_indexing: boolean;
	seo_rich_schema: boolean;
	seo_robots_txt: string | null;
	seo_external_links_follow: 'follow' | 'nofollow';
	comments_code: string | null;
	newsletter_code: string | null;

	color_modes: 'light' | 'dark' | 'both';
	color_mode_default: 'light' | 'dark' | 'os';

	syntax_on: boolean;
	syntax_line_numbers: boolean;
	syntax_theme: string | null;
	heading_anchors: boolean;

	flashload: boolean;
	variants: BlogVariant[];

	link_analysis_enabled: boolean;
	link_analysis_email_report: 'always' | 'broken' | 'never';

	ai_provider: 'mistral' | 'openai' | 'anthropic';
	ai_translation_enabled: boolean;
	ai_generation_enabled: boolean;

	hyvor_talk_enabled: boolean;
	hyvor_post_enabled: boolean;
}

export interface BlogCounts {
	posts: {
		published: number;
		draft: number;
		scheduled: number;
		featured: number;
	};
}

export interface HostingInfo {
	hosting_at: 'subdomain' | 'domain' | 'self';
	custom_domain?: CustomDomainSetup | null;
	custom_domain_intent?: CustomDomainIntent | null;
	hosting_url?: string;
	change?: HostingChange | null;
}

export type HostingChangeAt = 'subdomain' | 'domain' | 'self';
export type HostingChangeStatus = 'changing' | 'success' | 'failed';

export interface HostingChange {
	id: number;
	created_at: number;
	updated_at: number;
	from_at: HostingChangeAt;
	from_subdomain: string | null;
	from_domain: string | null;
	from_url: string;
	to_at: HostingChangeAt;
	to_subdomain: string | null;
	to_domain: string | null;
	to_url: string;
	status: HostingChangeStatus;
	error_message: string | null;
}

export type CustomDomainTlsProvider = 'auto' | 'custom';
export interface CustomDomainSetup {
	created_at: number;
	domain: string;
	tls_provider: CustomDomainTlsProvider;
	certificate: string | null;
	valid_from: number | null;
	valid_to: number | null;
}

// a pending, not-yet-DNS-verified auto-TLS custom domain setup
export interface CustomDomainIntent {
	created_at: number;
	domain: string;
}

export interface BlogIntegrations {
	hyvor_talk: null | {
		website_id: number;
	};
	hyvor_post: null | {
		newsletter_id: number;
	};
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

	variant_statuses: PostVariantStatusItem[];

	tags: Tag[];
	authors: User[];
};

export type PostStatus = 'draft' | 'published' | 'scheduled';

export type PostVariant = {
	id: number;
	language_id: number;
	slug: string | null;
	status: PostStatus;
	url: string;

	content: string | null;
	content_unsaved: string | null;
	title: string | null;
	description: string | null;
	content_updated_at: number | null;

	seo_primary_keyword: string | null;
	seo_secondary_keywords: string[];

	link_analysis: Record<string, number>;

	// Collaborative editing state (see PostVariantCollabService) - only populated on GET /post/{id}
	content_version: number;
	content_steps: Record<string, unknown>[];
	content_client_ids: string[];
	content_unsaved_version: number;
	content_unsaved_steps: Record<string, unknown>[];
	content_unsaved_client_ids: string[];
};

export type PostVariantStatusItem = {
	id: number;
	language_id: number;
	status: PostStatus;
	updated_at: number | null;
	content_updated_at: number | null;
	words: number | null;
};

// minimal shape used for listing posts/pages (GET /posts, GET /pages)
export type PostListItem = {
	id: number;
	created_at: number;
	updated_at: number;
	published_at: number | null;

	is_featured: boolean;
	is_page: boolean;

	slug: string | null;
	url: string | null;
	title: string | null;
	link_analysis: Record<string, number>;
	seo_score: number;

	variant_statuses: PostVariantStatusItem[];

	tags: string[];
	authors: string[];
};

export type UserStatus = 'invited' | 'active' | 'blocked';

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

	variants: UserVariant[];
};

export type UserVariant = {
	language_id: number;

	url: string;

	name: string | null;
	bio: string | null;
	location: string | null;
};

// === POST SUGGESTIONS (track-changes + comments, see @hyvor/richtext's EditorConfig.suggestions)

export type PostSuggestionReply = {
	id: string;
	author: string; // `user:<hyvor_user_id>`
	content: string;
	timestamp: number; // ms since epoch
};

export type PostSuggestionSourceEntry = {
	id: string;
	author: string; // `user:<hyvor_user_id>`
	timestamp: number; // ms since epoch
	comments: PostSuggestionReply[];
};

export type PostSuggestionAuthor = {
	name: string | null;
	picture_url: string | null;
};

// === TAG

export type Tag = {
	id: number;
	created_at: number;
	updated_at: number;
	is_private: boolean;

	slug: string;

	posts_count: number;
	code_head: string | null;
	code_foot: string | null;

	variants: TagVariant[];
};

export type TagVariant = {
	language_id: number;
	url: string;
	name: string | null;
	description: string | null;
};

// === LANGUAGE

export type Language = {
	id: number;
	code: string;
	name: string;
	is_primary: boolean;

	direction: 'ltr' | 'rtl';
};

// === MEDIA

export type Media = {
	id: number;
	uploaded_at: number;
	name: string;
	url: string;
	original_name: string;
	extension: string;
};

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
	dynamic: boolean;
	path: string;
	to: string;
	type: 'temporary' | 'permanent';
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
	is_enabled: boolean;
}

// === NAVIGATION
export interface Navigation {
	id: number;
	created_at: number;
	url: string;
	type: NavigationType;
	sort: number;
	variants: NavigationVariant[];
}

export type NavigationType = 'header' | 'footer';

export interface NavigationVariant {
	language_id: number;
	name: string | null;
}

// API

export interface ApiKey {
	id: number;
	name: string;
	type: ApiKeyType;
	api_key: string;
}

// WEBHOOK

export enum WebhookEventType {
	BLOG_UPDATED = 'blog.updated',

	POST_CREATED = 'post.created',
	POST_UPDATED = 'post.updated',
	POST_DELETED = 'post.deleted',

	TAG_CREATED = 'tag.created',
	TAG_UPDATED = 'tag.updated',
	TAG_DELETED = 'tag.deleted',

	USER_CREATED = 'user.created',
	USER_UPDATED = 'user.updated',
	USER_DELETED = 'user.deleted',

	MEDIA_CREATED = 'media.created',
	MEDIA_DELETED = 'media.deleted',

	NAVIGATION_CHANGED = 'navigation.changed',
	ROUTES_CHANGED = 'routes.changed',
	LANGUAGES_CHANGED = 'languages.changed',

	CACHE_SINGLE = 'cache.single',
	CACHE_TEMPLATES = 'cache.templates',
	CACHE_ALL = 'cache.all'
}

export interface Webhook {
	id: number;
	url: string;
	events: WebhookEventType[];
	secret: string;
}

export type WebhookDeliveryStatus = 'pending' | 'success' | 'failed';

export interface WebhookDelivery {
	id: number;
	url: string;
	event: string;
	status: WebhookDeliveryStatus;
	data: Record<string, any>;
	created_at: number;
}

export type ApiKeyType = 'console' | 'delivery';

// === THEME

export interface Theme {
	id: number;
	type: 'original' | 'ported';
	name: string;
	latest_version: string;
	preview_subdomain: string;
}

export type ThemeFolder = 'templates' | 'assets' | 'styles' | 'lang' | null;

export interface ThemeFile {
	id: number;
	name: string;
	content: string | null;
	folder: ThemeFolder;
}

export type JobStatus = 'pending' | 'completed' | 'failed';

export interface Export {
	id: number;
	created_at: number;
	format: 'hyvor_blogs' | 'wordpress';
	status: JobStatus;
	url: string | null;
	error: string | null;
}

export interface Import {
	id: number;
	created_at: number;
	name: string | null;
	type: 'sitemap' | 'wordpress';
	status: JobStatus;
	options: object;
	error: string | null;
	imported_counts: {
		posts: number;
		pages: number;
		tags: number;
		users: number;
	};
}

export type LinkAnalysisStatusType = 'ok' | 'redirect' | 'broken' | 'risky' | 'ignored';
export type LinkAnalysisIgnoreReason = 'known_firewall' | 'robots_txt' | 'internal_error';

export interface LinkAnalysisLink {
	id: number;
	url: string;
	full_url: string;
	status_code: number;
	status_type: 'ok' | 'redirect' | 'broken' | 'ignored';
	ignored: boolean;
	ignore_reason: LinkAnalysisIgnoreReason | null;
	comment: string | null;

	post_id: number;
	post_variant_id: number;
	post_variant_language_id: number;
	post_variant_title: string | null;
	post_variant_url: string | null;
}

export interface LinkAnalysisCheck {
	id: number;
	created_at: number;

	status: JobStatus;
	error: string | null;

	posts_count: number;

	links_total_count: number;
	links_ok_count: number;
	links_broken_count: number;
	links_risky_count: number;
	links_redirect_count: number;
	links_ignored_count: number;
}

export interface GptPrompt {
	id: number;
	created_at: number;
	post_id: number;

	prompt: string;
	gpt_response: string;
}

// === Hyvor Talk

export interface HyvorTalkGatedContentRule {
	id: number;
	tag: Tag;
	minimum_plan: string | null;
	gate: string | null;
}
