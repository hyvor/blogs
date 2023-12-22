// === BLOG ===
export enum BlogType {
    DEFAULT = 'default',
    DEV = 'dev',
    TEMP = 'temp'
};

export enum BlogHostingAt {
    SUBDOMAIN = 'subdomain',
    DOMAIN = 'domain',
    SELF = 'self'
}

export enum SeoExternalLinksFollow {
    FOLLOW = 'follow',
    NOFOLLOW = 'nofollow'
}

export enum CommentsType {
    HYVOR_TALK = 'ht',
    OTHER = 'other'
}

export enum ColorModes {
    LIGHT = 'light',
    DARK = 'dark',
    BOTH = 'both'
}

export enum ColorModeDefault {
    LIGHT = 'light',
    DARK = 'dark',
    OS = 'os'
}

// == USER ==
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