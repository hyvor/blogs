# Console API

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
* [Data Import](#data-import)
* [Data Export](#data-export)
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

* [`GET /posts`](#endpoint-posts-get) - Get posts
* [`GET /pages`](#endpoint-pages-get) - Get pages
* [`POST /post`](#endpoint-post-create) - Create a post/page
* [`GET /post/{id}`](#endpoint-post-get) - Get a post/page
* [`PATCH /post/{id}`](#endpoint-post-update) - Update a post/page
* [`DELETE /post/{id}`](#endpoint-post-delete) - Delete a post/page
* [`POST /post/{id}/variant`](#endpoint-post-variant-create) - Create a post language variant
* [`DELETE /post/{id}/variant`](#endpoint-post-variant-delete) - Delete a post language variant

Objects:



## Objects

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

<!--
#### Post Object {#post-object}

```json
{
    "id": 2000,
    "preview_id": "...",
    "created_at": 1639655890,
    "updated_at": 1639655890,
    "published_at": null,
    "status": "draft",
    "is_featured": false,
    "is_page": false,
    "slug": "hello-world",
    "content": "{}",
    "content_unsaved": "{}",
    "title": "Hello World",
    "description": "Just saying hello to the world",
    "url": "https://myblog.hyvorblogs.io/hello-world",
    "featured_image": "https://myblog.hyvorblogs.io/media/image.png",
    "canonical_url": null,
    "words": 500,
    "code_head": null,
    "code_foot": null,
    "tags": [ Tag Objects ],
    "authors": [ User Objects ]
}
```

#### GET /posts {#endpoint-posts}

This endpoint returns posts of the blog. You can use request params to filter results. 

Request Params:


| Name | Type | Description | Accepted Values
---|---|---|---|
`status` | `string` | Status of the post to filter | `all`, `published`, `draft`, `scheduled`, `featured` |
`author_id` | `null` or `integer` | Author ID to filter | |
`tag_id` | `null` or `integer` | Tag ID to filter | |
`language_id` | `null` or `integer` | Language ID to filter | |
`start_at` | `null` or `integer` | Start timestamp for filtering | UNIX Timestamp
`end_at` | `null` or `integer` | End timestamp for filtering | UNIX Timestamp
`search` | `null` or `string` | For searching | |

Response: An array of [Post Objects](#post-object)

#### GET /post/{id} {#endpoint-post}

Get a single post by ID. Returns a single post object.

#### PATCH /post/{id} {#endpoint-post-update}

Update a post by ID.

### Billing {#billing}

Endpoints

* [`GET /billing`](#endpoint-billing-get) - Get billing information (subscriptions, receipts, and usage)

Subscription create, update, and cancel endpoints cannot be access via API keys. Use our Console for those actions.

#### Subscription Info Object {#subscription-info-object}

```ts
interface SubscriptionInfo {

  email: string,
  
  card_brand: string,
  card_last_four: string,
  card_expiration: string,
  
  update_url: string,

  // Last payment amount as a float
  last_payment: number, 
  last_payment_at: number,

  // Next payment amount as float - null if subscription is cancelled
  next_payment: number  | null,
  next_payment_at: number | null

}
```

#### Subscription Object {#subscription-object}

```ts
interface Subscription {
    
    status: 'active' | 'past_due' | 'paused' | 'deleted',
    plan: 'A' | 'B' | 'C' | 'D' | 'E',
    frequency: 'monthly' | 'yearly',
    created_at: number,
  
    // UNIX timestamp if the subscription was cancelled, otherwise null
    ends_at: number | null,
  
    // whether the subscription is cancelled and in the grace period
    is_on_grace_period: boolean

}
```

#### Receipt Object {#receipt-object}

```ts
interface Receipt {
    id: number,
    paid_at: number,
    amount: number,
    tax: number,
    currency: number,
    receipt_url: string
}
```

### Usage Object {#usage-object}

```ts
interface Usage {
    current: number;
    total: number;
    percentage: number; // float
}
```

#### GET /billing {#endpoint-billing-get}

Request Params: *None*

Response:

```ts
interface Response {
    info: SubscriptionInfo,
    subscriptions: Subscription[],
    receipts: Receipt[],
    usage: {
        users: Usage,
        media: Usage
    }
}
```

-->