# Console API

> This documentation is still in progress.

Console API allows you to do administrative tasks of a blog. This is the same API we use internally in the Console. You can use it to automate some tasks or even build a completely new mini-console by yourself.

## Calling the API

* API Basepath: **https&#65279;//blogs.hyvor.com/api/console/v0/blog/{subdomain}**
* Create a Console API Key from the Console and send it as the **X-API-KEY** header.
* Console API endpoints use the following HTTP methods.
    * `GET` - to get data, usually an array of resources
    * `POST` - to create a resource
    * `PATCH` - to partially update a resource
    * `PUT` - to completely update an resource
    * `DELETE` - to delete a resource
* Similarly to our [Data API](api-data), the Console API always return an object or an array of objects, in JSON format
* Request params can be set as JSON (recommended) or as usual request params (in query or HTTP body)
* In this documentation, objects, request params, and responses are written as <a class="link" target="_blank" rel="nofollow" href="https://www.typescriptlang.org/">Typescript</a> interfaces in order to make type declarations concise.

## Authenticating User {#authenticating-user}

[To be written]

## Categories

The Console API is huge, and is categorized by what "resource" you want to access or manage. Most categories have CRUD operations but some may have more endpoints for specific tasks.  These objects are defined within the Category. Also, note that Console API objects are different from Data API objects.

Jump to each category:

* [Blog](#blog)
* [Posts & Pages](#posts)
* [Tags](#tags)
* [Users](#users)
* [Media](#media)
* [Navigation](#navigation)
* [Languages](#languages)
* [Redirects](#redirects)
* [Webhooks](#webhooks)
* [Theme Files](#theme-files)
* [Export](#data-export)
* [Link Analysis](#link-analysis)
* [Misc](#misc)

### Blog {#blog}

Endpoints:

* [`GET /blog`](#get-blog) - Get blog data
* [`PATCH /blog`](#update-blog) - Update blog data
* [`POST /blog/variant`](#create-blog-variant) - Create a blog variant
* [`PATCH /blog/variant`](#update-blog-variant) - Update a blog variant

Objects:

* [Blog](#blog-object)
* [BlogVariant](#blog-variant-object)

#### Get blog data {#get-blog}

`GET /blog`

```ts
type Request = {}
type Response = Blog
```

#### Update blog data {#update-blog}

`PATCH /blog`

```ts
type Request = Partial<Blog> // except id and variants
type Response = Blog
```

#### Create a blog variant {#create-blog-variant}

`POST /blog/variant`

```ts
type Request = {
    language_id: number
}
type Response = BlogVariant
```

#### Update a blog variant {#update-blog-variant}

`PATCH /blog/variant`

```ts
type Request = {
    language_id: number,
    name?: string,
    description?: string
}
type Response = BlogVariant
```

### Posts & Pages {#posts}

Endpoints:

* [`GET /posts`](#get-posts) - Get posts
* [`GET /pages`](#get-pages) - Get pages
* [`POST /post`](#create-post) - Create a post/page
* [`GET /post/{id}`](#get-post) - Get a post/page
* [`PATCH /post/{id}`](#update-post) - Update a post/page
* [`DELETE /post/{id}`](#delete-post) - Delete a post/page
* [`POST /post/{id}/variant`](#create-post-variant) - Create a post variant
* [`PATCH /post/{id}/variant`](#update-post-variant) - Update a post variant
* [`DELETE /post/{id}/variant`](#delete-post-variant) - Delete a post variant
* [`PATCH /post/{id}/tags`](#update-post-tags) - Update post tags
* [`PATCH /post/{id}/authors`](#update-post-authors) - Update post authors

Objects:

* [Post](#post-object)
* [PostVariant](#post-variant-object)

#### Get posts {#get-posts}

Get posts with filtering. The filter parameters are similar to the ones in the Console.

`GET /posts`

```ts
type Request = {
    status?: 'featured' | 'published' | 'draft' | 'scheduled',
    author_id?: number,
    tag_id?: number,
    start_timestamp?: number, // unix timestamp
    end_timestamp?: number, // unix timestamp
    search?: string,
    limit?: number, // default 50, max 100
    offset?: number,
}
type Response = Post[]
```

#### Get pages {#get-pages}

`GET /pages`

```ts
type Request = {}
type Response = Post[]
```

#### Create a post {#create-post}

Create an empty draft post. A post variant will be created from the primary language of the blog.

`POST /post`

```ts
type Request = {
    is_page?: boolean, // default to false
}
type Response = Post
```

#### Get a post/page {#get-post}

`GET /post/{id}`

```ts
type Request = {}
type Response = Post
```

#### Update a post/page {#update-post}

`PATCH /post/{id}`

```ts
type Request = {
    is_featured?: boolean,
    featured_image_url?: string | null,
    canonical_url?: string | null,
    code_head?: string | null,
    code_foot?: string | null,
    published_at?: number | null, // unix timestamp
}
type Response = Post
```

#### Delete a post/page {#delete-post}

`DELETE /post/{id}`

```ts
type Request = {}
type Response = {}
```

#### Create a post variant {#create-post-variant}

`POST /post/{id}/variant`

```ts
type Request = {
    language_id: number
}
type Response = PostVariant
```

#### Update a post variant {#update-post-variant}

`PATCH /post/{id}/variant`

```ts
type Request = {
    language_id: number,
    slug?: string, // max 255 chars
    status?: 'draft' | 'published' | 'scheduled',
    content?: string | null,
    content_unsaved?: string | null,
    title?: string | null, // max 255 chars
    description?: string | null, // max 255 chars
}
type Response = PostVariant
```

#### Delete a post variant {#delete-post-variant}

`DELETE /post/{id}/variant`

```ts
type Request = {
    language_id: number
}
type Response = {}
```

#### Update post tags {#update-post-tags}

`PATCH /post/{id}/tags`

```ts
type Request = {
    ids: number[] // tag IDs
}
type Response = {}
```

#### Update post authors {#update-post-authors}

`PATCH /post/{id}/authors`

```ts
type Request = {
    ids: number[] // author (user) IDs
}
type Response = {}
```

### Tags {#tags}

Endpoints:

* [`GET /tags`](#get-tags) - Get tags
* [`GET /tags/search`](#search-tags) - Search tags
* [`POST /tag`](#create-tag) - Create a tag
* [`PATCH /tag/{id}`](#update-tag) - Update a tag
* [`DELETE /tag/{id}`](#delete-tag) - Delete a tag
* [`POST /tag/{id}/variant`](#create-tag-variant) - Create a tag variant
* [`PATCH /tag/{id}/variant`](#update-tag-variant) - Update a tag variant
* [`DELETE /tag/{id}/variant`](#delete-tag-variant) - Delete a tag variant

Objects:

* [Tag](#tag-object)
* [TagVariant](#tag-variant-object)

#### Get tags {#get-tags}

`GET /tags`

```ts
type Request = {
    limit?: number, // default 50, max 100
    offset?: number,
}
type Response = Tag[]
```

#### Search tags {#search-tags}

Searches for tags by name (primary language).

`GET /tags/search`

```ts
type Request = {
    search: string,
}
type Response = Tag[]
```

#### Create a tag {#create-tag}

`POST /tag`

```ts
type Request = {
    name: string, // name for the primary language variant
}
type Response = Tag
```

#### Update a tag {#update-tag}

`PATCH /tag/{id}`

```ts
type Request = {
    slug?: string,
    code_head?: string | null,
    code_foot?: string | null,
}
type Response = Tag
```

#### Delete a tag {#delete-tag}

`DELETE /tag/{id}`

```ts
type Request = {}
type Response = {}
```

#### Create a tag variant {#create-tag-variant}

`POST /tag/{id}/variant`

```ts
type Request = {
    language_id: number,
}
```

#### Update a tag variant {#update-tag-variant}

`PATCH /tag/{id}/variant`

```ts
type Request = {
    language_id: number,
    name?: string,
    description?: string | null,
}
```

#### Delete a tag variant {#delete-tag-variant}

`DELETE /tag/{id}/variant`

```ts
type Request = {
    language_id: number,
}
```

### Users {#users}

Endpoints:

* [`GET /users`](#get-users) - Get users
* [`GET /users/search`](#search-users) - Search users
* [`POST /user`](#create-user) - Create a user
* [`POST /user/guest`](#create-guest-user) - Create a guest user
* [`PATCH /user/{id}`](#update-user) - Update a user
* [`DELETE /user/{id}`](#delete-user) - Delete a user
* [`POST /user/{id}/variant`](#create-user-variant) - Create a user variant
* [`PATCH /user/{id}/variant`](#update-user-variant) - Update a user variant
* [`DELETE /user/{id}/variant`](#delete-user-variant) - Delete a user variant
* [`POST /user`](#resend-invite) - Resend invitation email

Objects:

* [User](#user-object)
* [UserVariant](#user-variant-object)

#### Get users {#get-users}

`GET /users`

```ts
type Request = {
    offset?: number,
}
type Response = User[]
```

#### Search users {#search-users}

Searches for users by name.

`GET /users/search`

```ts
type Request = {
    search: string,
}
type Response = User[]
```

#### Create a user {#create-user}

`POST /user`

```ts
type Request = {
    username_or_email: string,
    role: 'owner' | 'admin' | 'editor' | 'writer' | 'contributor' | 'finance',
}
type Response = User
```

#### Create a guest user {#create-guest-user}

`POST /user/guest`

```ts

type Request = {
    name: string,
}
type Response = User
```

#### Update a user {#update-user}

`PATCH /user/{id}`

```ts
type Request = {
    hyvor_user_id?: number | null,
    role?: 'owner' | 'admin' | 'editor' | 'writer' | 'contributor' | 'finance' | null,
    status: 'active' | 'blocked',
    slug: string,
    email?: string | null,
    website_url?: string | null,
    picture_url?: string | null,
    social_facebook?: string | null,
    social_twitter?: string | null,
    social_linkedin?: string | null,
    social_youtube?: string | null,
    social_tiktok?: string | null,
    social_instagram?: string | null,
    social_github?: string | null,
}
type Response = User
```

#### Delete a user {#delete-user}

`DELETE /user/{id}`

```ts
type Request = {}
type Response = {}
```

#### Create a user variant {#create-user-variant}

`POST /user/{id}/variant`

```ts
type Request = {}
type Response = UserVariant
```

#### Update a user variant {#update-user-variant}

`PATCH /user/{id}/variant`

```ts
type Request = {
    name?: string | null,
    bio?: string | null,
    location?: string | null,
}
type Response = UserVariant
```

#### Delete a user variant {#delete-user-variant}

`DELETE /user/{id}/variant`

```ts
type Request = {}
type Response = {}
```

#### Resend email invitation {#resend-invite}

`POST /user/{id}/resend-invite`

```ts
type Request = {}
type Response = {}
```

### Media {#media}

Endpoints:

* [`GET /media`](#get-media) - Get medias
* [`POST /media`](#create-media) - Create a media
* [`POST /media/from-url`](#create-media-from-url) - Create a media from URL
* [`DELETE /media/{id}`](#delete-media) - Delete a navigation
* [`GET /media/unsplash/search`](#search-media-unsplash) - Get medias from unsplash

Objects:

* [Media](#media-object)

#### Get medias {#get-media}

`GET /media`

```ts
type Request = {
    limit: number,
    offset: number,
    search?: string | null,
    extensions?: string[] | null,
    type?: string | null
}
type Response = Media[]
```

#### Create a media {#create-media}

`POST /media`

```ts
type Request = {
    file: File,
    post_id: number
}
type Response = Media
```

#### Create a media from URL {#create-media-from-url}

`POST /media/from-url`

```ts
type Request = {
    url: string,
    post_id?: number | null
}
type Response = Media
```

#### Delete a media {#delete-media}

`DELETE /media/{id}`

```ts
type Request = {}
type Response = {}
```

#### Get medias from Unsplash {#search-media-unsplash}

`GET /media/unsplash/search`

```ts
type Request = {
    search: string,
    page: number
}
type Response = Media[]
```

### Navigation {#navigation}

Endpoints:

* [`GET /navigations`](#get-navigation) - Get navigations
* [`PATCH /navigations/sort`](#sort-navigations) - Update sort navigations
* [`POST /navigation`](#create-navigation) - Create a navigation
* [`PUT /user/{id}`](#update-navigation) - Update a navigation
* [`DELETE /navigation/{id}`](#delete-navigation) - Delete a navigation
* [`POST /navigation/{id}/variant`](#create-navigation-variant) - Create a navigation variant
* [`PUT /navigation/{id}/variant`](#update-navigation-variant) - Update a navigation variant
* [`DELETE /navigation/{id}/variant`](#delete-navigation-variant) - Delete a navigation variant

Objects:

* [Navigation](#navigation-object)
* [NavigationVariant](#navigation-variant-object)

#### Get navigations {#get-navigations}

`GET /navigations`

```ts
type Request = {}
type Response = Navigation[]
```

#### Update sort navigations {#sort-navigations}

`PATCH /navigations/search`

```ts
type Request = {
    ids?: number[],
}
type Response = {}
```

#### Create a navigation {#create-navigation}

`POST /navigation`

```ts
type Request = {
    url: string,
    name: string,
    type: 'header' | 'footer'
}
type Response = Navigation
```

#### Update a navigation {#update-navigation}

`PUT /navigation/{id}`

```ts
type Request = {
    url: string,
    name: string,
    type: 'header' | 'footer',
}
type Response = Navigation
```

#### Delete a navigation {#delete-navigation}

`DELETE /navigation/{id}`

```ts
type Request = {}
type Response = {}
```

#### Create a navigation variant {#create-navigation-variant}

`POST /navigation/{id}/variant`

```ts
type Request = {
    language_id: number,
    name?: string | null,
}
type Response = NavigationVariant
```

#### Update a navigation variant {#update-navigation-variant}

`PUT /navigation/{id}/variant`

```ts
type Request = {
    name: string | null,
}
type Response = NavigationVariant
```

#### Delete a navigation variant {#delete-navigation-variant}

`DELETE /navigation/{id}/variant`

```ts
type Request = {}
type Response = {}
```

### Language {#language}

Endpoints:

* [`GET /languages`](#get-languages) - Get languages
* [`POST /language`](#create-language) - Create a language
* [`PATCH /language/{id}`](#update-language) - Update a language
* [`DELETE /language/{id}`](#delete-language) - Delete a language

Objects:

* [Language](#language-object)

#### Get languages {#get-languages}

`GET /languages`

```ts
type Request = {}
type Response = Languages[]
```

#### Create a language {#create-language}

`POST /language`

```ts
type Request = {
    code: string, // max 12 chars
    name: string, // max 255 chars
    direction: 'ltr' | 'rtl',
}
type Response = Language
```

#### Update language {#updata-language}

`PATCH /language/{id}`

```ts
type Request = {
    code: string, // max 12 chars
    name: string, // max 255 chars
    direction: 'ltr' | 'rtl',
}
type Response = Language
```

#### Delete a language {#delete-language}

`DELETE /language/{id}`

```ts
type Request = {}
type Response = {}
```

### Redirect {#redirect}

Endpoints:

* [`GET /redirects`](#get-redirects) - Get redirects
* [`POST /redirect`](#create-redirect) - Create a redirect
* [`PUT /redirect/{id}`](#update-redirect) - Update a redirect
* [`DELETE /redirect/{id}`](#delete-redirect) - Delete a redirect

Objects:

* [Redirect](#redirect-object)

#### Get Redirects {#get-redirects}

`GET /redirects`

```ts
type Request = {
    limit?: number,
    offset?: number,
}
type Response = Redirect[]
```

#### Create a redirect {#create-redirect}

`POST /redirect`

```ts
type Request = {
    path: string,
    to: string,
    type: 'temporary' | 'permanent'
}
type Response = Redirect
```

#### Update a redirect {#updata-redirect}

`PUT /redirect/{id}`

```ts
type Request = {
    path?: string,
    to?: string,
    type?: 'temporary' | 'permanent'
}
type Response = Redirect
```

#### Delete a redirect {#delete-redirect}

`DELETE /redirect/{id}`

```ts
type Request = {}
type Response = {}
```

### Webhook {#webhook}

Endpoints:

* [`GET /webhooks`](#get-webhooks) - Get webhooks
* [`POST /webhook`](#create-webhook) - Create a webhook
* [`PATCH /webhook/{id}`](#update-webhook) - Update a webhook
* [`DELETE /webhook/{id}`](#delete-webhook) - Delete a webhook

Objects:

* [Webhook](#webhook-object)

#### Get webhook {#get-webhook}

`GET /webhook`

```ts
type Request = {}
type Response = Webhook[]
```

#### Create a webhook {#create-webhook}

`POST /webhook`

```ts
type Request = {
    url: string,
    events: 'cache.single' | 'cache.templates' | 'cache.all'[],
}
type Response = Webhook
```

#### Update a webhook {#updata-webhook}

`PATCH /webhook/{id}`

```ts
type Request = {
    url?: string,
    events?: 'cache.single' | 'cache.templates' | 'cache.all'[],
}
type Response = Webhook
```

#### Delete a webhook {#delete-webhook}

`DELETE /webhook/{id}`

```ts
type Request = {}
type Response = {}
```
### Theme files {#theme-files}

Endpoints:

* [`GET /theme/files`](#get-theme-files) - Get theme files
* [`POST /theme/file`](#create-theme-file) - Create a theme file
* [`PATCH /theme/file/{id}`](#update-theme/file) - Update a theme file
* [`DELETE /theme/file/{id}`](#delete-theme-file) - Delete a theme file

Objects:

* [FileObject](#file-object)

#### Get theme files {#get-theme-files}

`GET /theme/files`

```ts
type Request = {}
type Response = FileObject[]
```

#### Create a theme file {#create-theme-file}

`POST /theme/file`

```ts
type Request = {
    folder: 'templates' | 'assets' | 'styles' | 'lang',
    name: string,
    content?: string,
    file: File
}
type Response = FileObject
```

#### Update a theme file {#updata-theme-file}

`PATCH /theme/file/{id}`

```ts
type Request = {
    name?: string,
    content?: string | null
}
type Response = FileObject
```

#### Delete a theme file {#delete-theme-file}

`DELETE /theme/file/{id}`

```ts
type Request = {}
type Response = {}
```

### Export {#export}

Endpoints:

* [`GET /exports`](#get-exports) - Get exports
* [`POST /export`](#create-export) - Create an export

Objects:

* [ExportObject](#export-object)

#### Get exports {#get-exports}

`GET /exports`

```ts
type Request = {}
type Response = ExportObject[]
```

```ts
type Request = {}
type Response = ExportObject
```

### Link Analysis {#link-analysis}

Endpoints:

* [`POST /link-analysis/check-urls`](#check-variant-urls) - Check post variant link
* [`PATCH /link-analysis/ignore-link`](#ignore-link) - Ignore a link
* [`GET /link-analysis/stats`](#get-link-stats) - Get link statistics
* [`GET /link-analysis/analyses`](#get-link-analyses) - Get link analyses
* [`GET /link-analysis/links`](#get-links) - Get links
* [`GET /link-analysis/checks`](#get-cheks) - Get checks
* [`POST /link-analysis/check`](#create-check) - Create a check


Objects:

* [Link](#link-object)
* [Check](#check-object)

#### Check post variant link {#check-variant-urls}

`POST /link-analysis/check-urls`

```ts
type Request = {
    post_variant_id: number,
    urls: string[]
    force?: boolean
}
type Response = LinkObject[]
```

#### Ignore a link {#ignore-link}

`PATCH /link-analysis/ignore-link`

```ts
type Request = {
    post_variant_id: number,
    urls: string[],
    status: boolean
}
type Response = LinkObject
```

#### Get link statistics {#get-link-stats}

`GET /link-analysis/stats`

```ts
type Request = {}
type Response = {
    counts: number
}
```

#### Get links {#get-links}

`GET /link-analysis/links`

```ts
type Request = {
    type?: 'ok' | 'broken' | 'ignored' | 'redirected',
    limit?: number,
    offset?: number,
}
type Response = LinkObject[]
```

#### Get checks {#get-checks}

`GET /link-analysis/checks`

```ts
type Request = {
    limit?: number,
    offset?: number,
}
type Response = CheckObject[]
```

#### Create a check {#create-check}

`GET /link-analysis/checks`

```ts
type Request = {}
type Response = CheckObject
```

### Route {#route}

Endpoints:

* [`GET /routes`](#get-routes) - Get routes
* [`POST /route`](#create-route) - Create a route
* [`PATCH /route/{id}`](#update-route) - Udpate a route
* [`DELETE /route/{id}`](#delete-route) - Delete a route

Objects:

* [Route](#route-object)

#### Get routes {#get-routes}

`GET /routes`

```ts
type Request = {}
type Response = Route[]
```

#### Create a route {#create-route}

`POST /route`

```ts
type Request = {
    name: string,
    match: string,
    template: string,
    post_filter?: string,
    content_type?: string
}
type Response = Route
```

#### Update a route {#update-route}

`PATCH /route/{id}`

```ts
type Request = {
    name: string,
    match: string,
    template: string,
    post_filter?: string,
    content_type?: string
}
type Response = Route
```

#### Delete a route {#delete-route}

`DELETE /route/{id}`

```ts
type Request = {}
type Response = {}
```

### Misc {#misc}

Endpoints:

* [`GET /misc/themes`](#get-all-themes) - Get all themes
* [`DELETE /blog/cache`](#delete-cache) - Delete blog cache

Objects:

* [Theme](#theme-object)

#### Get all themes {#get-all-themes}

`GET /misc/themes`

```ts
type Request = {}
type Response = Theme[]
```

#### Delete blog cache {#delete-cache}

`DELETE /blog/cache`

```ts
type Request = {
    type: 'all' | 'template' | 'paths',
    paths?: string[],
}
type Response = {}
```

## Objects {#objects}

### Blog Object {#blog-object}

```ts
interface Blog {
    id: number,
    created_at: number,
    is_blocked: boolean,
    subdomain: string,
    type: 'default' | 'dev',
    hosting_at: 'subdomain' | 'domain' | 'self',
    hosting_domain: string | null,
    hosting_url: string | null,

    embeddable: boolean,
    embedding_domains: string | null,

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
    seo_external_links_follow: 'follow' | 'nofollow',
    comments_code: string | null,
    newsletter_code: string | null,

    color_modes: 'light' | 'dark' | 'both',
    color_mode_default: 'light' | 'dark' | 'os',

    syntax_on: boolean,
    syntax_line_numbers: boolean,
    syntax_theme: string | null

    flashload: boolean,
    variants: BlogVariant[]
}
```

### Blog Variant Object {#blog-variant-object}

```ts
interface BlogVariant {
    language_id: number,
    name: string | null,
    description: string | null,
}
```


### Post Object {#post-object}

```ts
interface Post {
    id: number,
    preview_id: string,
    created_at: number,
    updated_at: number,
    published_at: number | null,

    is_featured: boolean,
    is_page: boolean,

    featured_image_url: string | null,
    canonical_url: string | null,
    code_head: string | null,
    code_foot: string | null,

    variants: PostVariant[],

    tags: Tag[],
    authors: User[]
}
```

### Post Variant Object {#post-variant-object}

```ts
interface PostVariant {
    language_id: number,
  
    slug: string | null,
    status: 'draft' | 'published' | 'scheduled',
    url: string,

    content: string | null,
    content_unsaved: string | null,
    title: string | null,
    description: string | null,
}
```

### Tag Object {#tag-object}

```ts
interface Tag {
    id: number,
    created_at: number,
    updated_at: number,
    slug: string,
    posts_count: number,
    code_head: string | null,
    code_foot: string | null,

    variants: TagVariant[]
}
```

### Tag Variant Object {#tag-variant-object}

```ts
interface TagVariant {
    language_id: number,
    url: string | null,
    name: string | null,
    description: string | null,
}
```

### User Object {#user-object}

```ts
interface User {
    id: number,
    created_at: number,
    updated_at: number,

    hyvor_user_id: number | null,

    status: 'invited' | 'active' | 'blocked',
    role: 'owner' | 'admin' | 'editor' | 'writer' | 'contributor' | 'finance',
    slug: string,
    posts_count: number,
    email: string,

    picture_url: string | null,
    website_url: string | null,

    social_facebook: string | null,
    social_twitter: string | null,
    social_linkedin: string | null,
    social_youtube: string | null,
    social_tiktok: string | null,
    social_instagram: string | null,
    social_github: string | null,

    variants: UserVariant[]
}
```

### User Variant Object {#user-variant-object}

```ts
interface UserVariant {
    language_id: number,
    url: string,
    name: string | null,
    bio: string | null,
    location: string | null,
}
```

### Media Object {#media-object}

```ts
interface Media {
    id: number,
    uploaded_at: number,
    name: string,
    url: string,
    original_name: string,
    extension: string
}
```

### Navigation Object {#navigation-object}

```ts
interface Navigation {
    id: number;
    created_at: number;
    url: string;
    type: NavigationType,
    sort: number;
    variants: NavigationVariant[]
}
```

### Navigation Variant Object {#navigation-variant-object}

```ts
interface NavigationVariant {
    language_id: number,
    name: string | null
}
```

### Language Object {#language-object}

```ts
interface Language {
    id: number,
    code: string,
    name: string,
    is_primary: boolean
}
```

### Redirect Object {#redirect-object}

```ts
interface Redirect {
    id: number,
    created_at: number,
    path: string,
    to: string,
    type: 'temporary' | 'permanent'
}
```

### Route Object {#route-object}

```ts
interface Route {
    id: number,
    created_at: number,
    name: string,
    match: string,
    template: string,
    posts_filter: string | null,
    content_type: string | null,
    is_enabled: boolean
}
```

### Webhook Object {#webhook-object}

```ts
interface Webhook {
    id: number,
    url: string,
    events: string[],
    secret: string,
}
```

### File Object {#file-object}

```ts
interface FileObject {
    id: number,
    name: string,
    content?: string
    folder: 'templates' | 'assets' | 'styles' | 'lang'
}
```

### Export Object {#export-object}

```ts
interface Export {
    id: number,
    createdf_at: number,
    format: 'hyvor_blogs' | 'wordpress',
    status: 'pending' | 'completed' | 'failed',
    url?: string,
    error?: string
}
```

### Theme Object {#theme-object}

```ts
interface Theme {
    id: number,
    type: 'original' | 'ported',
    name: string
}
```

### Link Object {#link-object}

```ts
interface LinkObject {
    id: number,
    url: string,
    full_url: string,
    status_code: number,
    status_type: 'ok' | 'broken' | 'redirect' | 'ignored',
    ignored: boolean,
    post_id: number,
    post_variant_id: number,
    post_variant_language_id: number,
    post_variant_title: string,
}
```

### Check Object {#check-object}

```ts
interface CheckObject {
    id: number,
    created_at: number,
    status: 'pending' | 'completed' | 'failed',
    error?: string,
    post_count: number,
    post_variants_count: number,
    page_count: number,
    page_variants_count: number,
    links_total_count: number,
    links_ok_count: number,
    links_broken_count: number,
    links_redirect_count: number,
    links_ignored_count: number,
}
```