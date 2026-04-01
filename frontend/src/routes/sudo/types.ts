export interface SudoConfig {
	hyvor: {
		instance: string;
	};
	app: {
		delivery_url: string;
	}
}

export interface SudoStats {
	total_blogs: number;
	total_30d_change: number;
	blogs_with_custom_domains: number;
}

export interface BlogVariant {
	id: number;
	blog_id: number;
	language_id: number;
	name: string | null;
	description: string | null;
}

export interface Organization {
	id: number;
	name: string;
	billing_email: string | null;
	billing_address: {
		country: string | null;
	} | null;
}

export interface Blog {
	id: number;
	created_at: number;
	updated_at: number | null;
	ip: string | null;
	is_blocked: boolean;
	blocked_at: number | null;
	hyvor_user_id: number | null;
	theme_version_id: number | null;
	subdomain: string;
	type: string | null;
	hosting_at: string;
	hosting_domain: string | null;
	hosting_url: string | null;
	hosting_redirect_subdomain: boolean | null;
	meta: Record<string, any> | null;
	counts: Record<string, number> | null;
	organization_id: number | null;
	variants: BlogVariant[];
}
