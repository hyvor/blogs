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
* [Billing](#billing)
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