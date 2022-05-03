
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

    variants: {[key: number]: UserVariant};
};

export type UserVariant = {
    language_id: number;

    name: string | null;
    bio: string | null;
    location: string | null;
}

export enum UserStatus {
    INVITED = 'invited',
    ACTIVE = 'active',
    BLOCKED = 'blocked'
}

export enum UserRole {
    OWNER = 'owner',
    ADMIN = 'admin',
    EDITOR = 'editor',
    WRITER = 'writer',
    CONTRIBUTOR = 'contributor',
    FINANCE = 'finance'
}