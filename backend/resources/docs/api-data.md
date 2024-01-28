# Data API

The Data API returns the **public data** of the blog.

* No API keys are required.
* All responses are in the JSON format.
* All endpoints use the HTTP `GET` method.
* The base path is: `https://blogs.hyvor.com/api/data/v0/{subdomain}`
  * Replace `{subdomain}` with the subdomain of your blog.

> In addition to calling the Data API via HTTP, it is possible call it within template files using the Twig [data() function](themes-templates#fetch-data). It is the preferred method if you want data to render some UI (Ex: recent posts section) in your blog, because the `data()` function calls the Data API internally at the time of rendering the template, eliminating the need for additional HTTP requests.

## Endpoints {#endpoints}

**Single-object**

- `/post` - a post/page
- `/tag`
- `/author`
- `/blog` - blog settings

**Multi-object**

- `/posts`
- `/posts/search` - search posts
- `/tags`
- `/authors`

## Response {#response}

For single-object endpoints, the response is an object. For example, `/post` endpoint returns a `Post` object (See below for object definitions).

```json
// A Post Object
{
	"id": 1000,
    "slug": "post",
	...
}
```

For multi-object endpoints, the response looks like this:

```json
{
	"data": [{}, {}], // array of objects
	"pagination": {} // a Pagination object
}
```


## Request {#request}

#### Single-Object endpoints {#single-object}

For `/post`, `/tag`, and `/author`

| Param | Description | Type
| --- | --- | --- | --- |
| `id` |  id of the object | `integer`
| `slug` | slug of the object | `string`
| `language` | See [language param](#language) | `string`
| `keys` | See [keys param](#keys) | `string`

> Either the `id` or the `slug` is required for those endpoints.

The `/blog` endpoint only takes `language` and `keys` as an input.

#### Multi-object endpoints {#multi-object}

`/posts`, `/posts/search`, `/tags`, and `/authors`

| Param      | Description                     | Type                       | Default  |
|------------|---------------------------------|----------------------------|----------|
| `language` | See [language param](#language) | `string` |          |
| `limit`    | See [limit param](#limit)       | `integer`                  | `25`     |
| `page`     | See [page param](#page)         | `integer`                  | `1`      |
| `filter`   | See [filter param](#filter)     | `string`                   | `""`     |
| `sort`     | See [sort param](#sort)         | `string`                   | [VARIES] |
| `keys`     | See [keys param](#keys)         | `string`                   |          |

The `/posts/search` endpoint has a required `search` param in addition to the above params.

| Param | Description                     | Type      | Default               |
| --- |---------------------------------|-----------|-----------------------|
| `search` | Value to search                 | `string`  |             |

### `language` param {#language}

If your blog has [multiple languages](languages), you can set the `language` param to a **language code** (ex: `en`, `fr`) of a language in your blog. If this param is not provided, the primary language of the blog is used. That language will be used to localize strings in posts, authors, tags, and the blog.

> **Note**: There is an important distinction between posts (`/post`, `/posts`, and `/posts/search`) and other endpoints when using languages. Let's say you have two languages in your blog: `en` (primary) and `fr`. If you call the `/posts` endpoint with language the language code `fr`, **only the posts that have a `fr` variant** will be returned. However, in other endpoints (authors, tags), all records will be returned regardless of they have a `fr` variant or not. Missing translations will be filled with primary language strings.
> 
> The reason is that, when someone visits your blog's `/fr` index page, we only want to show the posts that are translated into French. We do not want to "fallback" post contents. However, fallbacking author/tags data is fine in most cases.
> 
> ----
>
> In other words, `language` in post(s) endpoints works as a filter, while it works as a translator in other endpoints.

### `limit` param {#limit}

The `limit` param can be used to limit the number of records returned in multi-object endpoints. The default is `25`. Max is `250`.

### `page` param {#page}

The `page` param can be used to paginate results. This works in combination with the limit param. The default value is `1`.

```plain
To get the first 20 results:
/posts?limit=20

To get the next 20 results (page 2):
/posts?limit=20&page=2
```



### `filter` param {#filter}

Example: `(published_at > 1639665890 & published_at < 1639695890) | is_featured=true`

Our Data API uses [Laravel FilterQ](https://github.com/hyvor/laravel-filterq) under the hood, which allows you to write advanced logic like the above example, using comparison and logical operators.

A condition consists of three parts:

- `key`
- `operator`
- `value`

#### Operators

- `=` - equals
- `!=` - not equal
- `>` - greater than
- `<` - less than
- `>=` - greater than or equals
- `<=` - less than or equals

#### Values

- `null`
- bool: `true` or `false`
- string: `'hello'` or `hello`
    - Strings without quotes should match `[a-zA-Z_][a-zA-Z0-9_-]+` and cannot be `true`, `false`, or `null`.
- numbers: `250`, `-250`, `2.5`

#### Logical Operators

You can use Logical Operators to combine multiple conditions.

- `&` - AND
- `|` - OR

Please see the [FilterQ Expressions](https://github.com/hyvor/laravel-filterq#filterq-expressions) documentation if you need more details.
#### Supported Keys for Filtering


| Endpoint | Key                  | Supported Operators | Value Type | Description |
| --- |----------------------| --- | --- | --- |
| `/posts` | `id`                 | all | `integer` |  |
|  | `published_at`       | all | `date` | See [Date](#filter-date) |
|  | `updated_at`         | all | `date` | See [Date](#filter-date) |
| | `created_at`         | all | `date` | See [Date](#filter-date) |
|  | `is_featured`        |`=`, `!=` | `boolean` |  |
|  | `slug`               | `=`, `!=` | `string` |  |
|  | `featured_image_url` | `=`, `!=` | `null` | only to check if null or not |
|  | `canonical_url`      | `=`, `!=` | `null` | only to check if null or not |
|  | `words`              | all | `integer` |  |
|  | `tag.id`             | all | `integer` | Matches the id of the tags of the post. |
|  | `tag.slug`           | `=`, `!=` | `string` | Matches the slug of the tags of the post. |
|  | `author.id`          | all | `integer` | Similar to tag.id |
|  | `author.slug`        | `=`, `!=` | `string` | Similar to tag.slug |
| `/tags` and `/authors` | `id`                 | all | `integer` |  |
|  | `slug`               | `=`, `!=` | `string` |  |
|  | `posts_count`        | all | `integer` |  |
| | `created_at`         | all | `date` | See [Date](#filter-date) |

#### Date Values {#filter-date}

Here are some valid values for date keys.

* `'2022-01-01'`
* `'yesterday'`
* `'first day of this year'`
* `'last day of next month'`
* `'+1 day'`
* `'-1 week'`
* `'next Thursday'`
* `1639655890` <- UNIX Timestamp

For example: In `/posts` endpoint, you may use `published_at>'-7 days'` to get posts published in the last 7 days.
#### Filtering Examples

> Note that when calling the API via HTTP, the filter value should be URL-encoded.

To get posts authored by Alex:

```js
// filter
author.slug=alex

// URL-encoded
/posts?filter=author.slug%3Dalex
```

To get featured posts

```js
// filter
is_featured=true

// URL-encoded
/posts?filter=is_featured%3Dtrue
```

To get posts with either the tag `audio` or `video`.

```js
// filter
tag.slug=audio|tag.slug=video

// URL-encoded
/posts?filter=tag.slug%3Daudio%7Ctag.slug%3Dvideo
```

To get tags that have at least 5 posts

```js
// filter
posts_count>=5

// URL-encoded
/tags?filter=posts_count%3E%3D5
```

To get authors who are added after January 1st 2020.

```js
// filter
created_at>='2020-01-01'

// URL-encoded
/authors?filter=created_at%3E%3D%272020-01-01%27
```

To get posts published in the last 7 days.

```js
// filter
published_at>'-7 days'

// URL-encoded
/posts?filter=published_at%3E%27-7%20days%27
```

### `sort` param {#sort}

Here's a list of supported sort values. You can combine multiple as comma-separated-values, which then will be executed in its order, similar to `ORDER BY` in SQL.

| Endpoint | Sort | Description |
| --- | --- | --- |
| `/posts` <br> Default `published_at DESC`  | `published_at` |  Post publish time |
| | `created_at` | Post create time |
| | `updated_at` | Post last update time |
| | `id` | Post ID |
| | `is_featured` | Think of this as an integer, 1 for true and 0 for false . |
| | `title` | alphabetically |
| | `words` |  |
| `/tags` and `/authors` <br> Default `posts_count DESC` | `posts_count` | number of posts of the tag/author |
| | `created_at` | |

The default sort method is `DESC`. Here are some examples for the sort param.

- `published_at` - sorted by `published_at` in descending order
- `published_at ASC` - sorted by `published_at` in ascending order
- `is_featured DESC, published_at DESC` - featured posts first, then ordered by publish time in descending order. `DESC` is optional. `is_featured, published_at` is identical with the former.

### `keys` param {#keys}

The `keys` can be used to include or exclude keys from the Objects, similar to GraphQL. All endpoints support the `keys` param.

If you call the `/posts` endpoint, with `keys=id,content`, the post objects will only contain those two keys.

```json
{
	"id": 1000,
	"content": "<p></p>"
}
```

Use `!` at the start to exclude tags. For example, `keys=!content,description` will exclude `content` and `description` from the Post object and all other keys will be included.

Let's say you only want to get the post ID and tag ID of the posts. Use `keys=id,tags.id`. You will get objects like this.

```json
{
	"id": 1000,
	"tags": [
		{
			"id": 2000
		}
	]
}
```


## Objects {#objects}

> All timestamps are in **[Unix Timestamp](https://www.unixtimestamp.com/)** format (integer).

### Post Object {#post-object}

```json
{
	"id": 1000,
	
	"created_at": 1639655890,
	"updated_at": 1639655890,
  	"published_at": 1639665890,

	"is_featured": false,
	"is_page": false,
  	"slug": "hello-world",
	"content": "<p></p>",
	"title": "Hello World",
	"description": "This is a hello world page",
	"url": "https://subdomain.hyvorblogs.io/hello-world",
	"featured_image_url": "https://example.com/image.png",
	"canonical_url": null,
	"words": 500,
	"code_head": "",
	"code_foot": "",
	
	"language": language object,
	"variants": [ variant objects ],

	"tags": [ tag objects ],
	"authors": [ author objects ]
}
```

| Key               | Type | Description                                                                              |
|-------------------| --- |------------------------------------------------------------------------------------------|
| `id`              | `integer` | A unique ID for the post                                                                 |
| `created_at`      | `integer` | The time the post was created (as a draft)                                               |
| `updated_at`      | `integer` | The time the post or its meta data was updated                                           |
| `published_at`    | `integer` | Publish time of the post.                                                                |
| `is_featured`     | `boolean` | Whether the post is featured. There can be multiple featured posts on a blog             |
| `is_page`         | `boolean` | Whether it is a page. See [Posts & Pages](writing#posts-pages)                           |
| `slug`            | `string` | The URL slug of the post                                                                 |
| `url`             | `string` | The absolute URL of the post, generated based on where the blog is hosted.               |
| `content`         | `string` | The post content in HTML. See [Content & The Editor](writing) to see supported HTML tags |
| `title`           | `string` | The title of the post, max length 256                                                    |
| `description`     | `string| null`                                                                                    | The description (excerpt) of post, max length 350, null if not set |
| `featured_image_url` | `string| null`                                                                                    | The absolute URL of the featured image. null if not set |
| `canonical_url`   | `string| null`                                                                                    | An absolute URL or null. Canonical URL is set by the author if the post was published somewhere else. |
| `words`           | `integer` | Number of words in the content                                                           |
| `code_head`       | `string` | [Custom code](custom-code) to add before `</head>` . An empty string if nothing is set.  |
| `code_foot`       | `string` | [Custom code](custom-code) to add before `</body>` . An empty string if nothing is set.  |
| `language`        | `object` | A [Language object](#language-object)                                                    
| `variants`        | `array` | An array of [Variant objects](#variant-object).                                          |
| `tags`            | `array`  | An array of [Tag objects](#tag-object). The primary tag is the index 0                   |
| `authors`         | `array` | An array of [Author objects](#author-object). The primary author is the index 0          |

> In posts, **id** attribute is globally unique within Hyvor Blogs. The **slug** attribute is unique within the blog.

### Tag Object {#tag-object}

```json
{
	"id": 2000,
    "created_at": 1639655890,
	"name": "Hello World",
    "description": "Saying hello to the world",
	"slug": "hello-world",
	"url": "https://subdomain.hyvorblogs.io/tag/hello-world",
	"posts_count": 20,

	"language": language object,
	"variants": [ variant objects ],
}
```

| Key | Type | Description                                                 |
| --- | --- |-------------------------------------------------------------|
| `id` | `integer` | A unique ID for the tag                                     |
| `created_at`      | `integer` | The time the tag was created                                |
| `name` | `string` | Name (or title) of the tag                                  |
| `description` | `string| null`                                                       | Description of the tag |
| `slug` | `string` | URL slug of the tag (full default path will be `/tag/{slug}`) |
| `url` | `string` |                                                             |
| `posts_count` | `integer` | Number of posts of the tag                                  |
| `language` | `object` | A [Language object](#language-object)                       
| `variants` | `array` | An array of [Variant objects](#variant-object)              |

### Author Object {#author-object}

> Author is a [user](users) who has written at least one post

```json
{
	"id": 3000,
    "created_at": 1639655890,
	"slug": "blogger",
	"url": "https://subdomain.hyvorblogs.io/author/blogger",
	"name": "Blogger",
	"picture_url": "https://example.com/image.png",
	"bio": "I am a blogger",
	"website_url": "https://example.com",
	"location": "France",
	"social": social media object,
	"posts_count": 32,

	"language": language object,
	"variants": [ variant objects ],
}
```

| Key | Type | Description                                                         |
| --- | --- |---------------------------------------------------------------------|
| `id` | `integer` | A unique ID for the author                                          |
| `created_at`      | `integer` | The time the user was created                                       |
| `slug` | `string` | URL slug of the author (full default path will be `/author/{slug}`) |
| `url` | `string` | Full URL of the user                                                |
| `name` | `string` | Author's name. Max length 50                                        |
| `picture_url` | `string| null`                                                               | The absolute URL of the author's picture. Usually, a small squared image |
| `bio` | `string| null`                                                               | Author's bio (max 256) |
| `website_url` | `string| null`                                                               | The absolute URL of the author's website |
| `location` | `string| null`                                                               | Author’s location. Max length 30 |
| `social` | `object` | A [Social Media object](#social-media-object)                       |
| `posts_count` | `integer` | number of posts written by the author                               |
| `language` | `object` | A [Language object](#language-object)                               
| `variants` | `array` | An array of [Variant objects](#variant-object).                     |

### Blog Object {#blog-object}

```json
{
	"subdomain": "alex",
	"name": "My Blog", 
	"description": "This is my blog hosted on Hyvor Blogs",
	"logo_url": "https://blog.hyvorblogs.io/media/logo.png",
    "icon_url": "https://blog.hyvorblogs.io/media/icon.png",
	"cover_url": "https://blog.hyvorblogs.io/media/cover.png",
	"url": "https://blog.hyvorblogs.io",
	"social": social media object,
	"nav_header": [
		{
			"name": "Home",
			"url": "/"
		},
		{
			"name": "About",
			"url": "/about"
		}
	],
	"nav_footer": [
		{
			"name": "Privacy",
			"url": "/privacy"
		}
	],

	"languages": [ language objects ],

	"code_head": "",
	"code_foot": "",

	"posts_count": 200,
    
    // the following are blog settings
    // which are used for generating header code and color themes
    "seo_indexing": true,
    "color_modes": "light",
    "color_mode_default": "light"
}
```

| Key                        | Type               | Description                                                     |
|----------------------------|--------------------|-----------------------------------------------------------------|
| `subdomain`                | `string`           | Subdomain of the blog                                           |
| `name`                     | `string`           | Name/title of the blog                                          |
| `description`              | `string            | null`                                                           | A short description of the blog (256 max)                      |
| `logo_url`                 | `string            | null`                                                           | The absolute URL of the blog icon. Usually, a small square image. |
| `cover_url`                | `string            | null`                                                           | The absolute URL of the featured/cover image                   |
| `url`                      | `string`           | Absolute URL of the blog for the current language.              |
| `base_url`                  | `string`           | Absolute URL of the blog.                                       |
| `social`                   | `object`            | A [Social Media object](#social-media-object)                                                         |
| `nav_header`, `nav_footer` | `array of objects` | Navigation links for the blog header and the footer.            |
| `languages`                | `array of objects` | All available languages of the blog. See [language object](#language-object).                           
| `code_head`, `code_foot`   | string             | Custom HTML code for before `</head>`, and `</body>` for all pages. |
| `posts_count`              | int                | Total published posts                                           |

### Language Object {#language-object}

```json
{
	"id": 1000,
	"code": "en",
	"name": "English",
	"is_primary": true,
    "direction": "ltr"
}
```

| Key | Type | Description |
| --- | --- | --- |
| `id` | `integer` | A unique ID for the language |
| `code` | `string` | Language code |
| `name` | `string` | Language name |
| `is_primary` | `boolean` | Whether the language is the primary language of the blog |
| `direction` | `string` | `ltr` or `rtl` |

### Variant Object {#variant-object}

A variant object contains data of a language variant of a post, tag, or an author.

```json
{
	"language": {
		"id": 1001,
		"code": "fr",
		"name": "French",
		"is_primary": false,
        "direction": "ltr"
	},
	"url": "https://subdomain.hyvorblogs.io/fr/hello-world"
}
```


| Key | Type | Description |
| --- | --- | --- |
| `language` | `object` | a [Language Object](#language-object) |
| `url` | `string` | URL of the variant |
### Pagination Object {#pagination-object}

A pagination object is included in all multi-object endpoints (`/posts`, `/authors`, `/tags`).

```json
{
	"total": 100,
	"pages": 10,
	"limit": 5,
	"page": 1,
    "page_prev": null,
    "page_next": 2,
}
```

| Key | Type | Description |
| --- | --- | --- |
| `total` | `integer` | The total number of results possible with the current filters |
| `pages` | `integer` | The number of the total pagination pages based on the limit you set. `pages = round_to_upper(total/limit)` |
| `limit` | `integer` | Current limit |
| `page` | `integer` | Current page |
| `page_prev` | `integer` or `null` | Previous page number (`null` if no previous pages)
| `page_next` | `integer` or `null` | Next page number (`null` if no more pages)

### Social Media Object {#social-media-object}

```json
{
    "facebook": null,
    "twitter": "https://twitter.com/HyvorBlogs",
    "linkedin": "https://www.linkedin.com/company/30240435",
    "youtube": null,
    "instagram": null,
    "github": "https://github.com/hyvor",
    "tiktok": null
}
```

## Error Handling {#error-handling}

In case of an error, the HTTP status code will be a non-200 status code. 

For 4xx errors, the response will be a JSON object.

```json
{
    "error": "ID is required",
    "error_code": "422"
}
```

These HTTP codes are possible:

* **404 Not Found** - Resource not found
  * 404 can be returned in a single-object endpoints when the object is not found
  * Make sure ID/slug (and language for posts) is correct
* **422 Unprocessable Entity** - Invalid input
  * Check the query params
  * You can find more details in the JSON output of the error

5xx errors means something is wrong on our side. Check our [status page](https://blogs.hyvor.com) for any downtimes. If the issue persists, [contact us](support).

## Pages {#pages}

We do not have separate endpoints to fetch [Pages](writing#posts-pages).

* To get a single page, call the `/post` endpoint with the page ID or slug.
* To get multiple pages, call the `/posts` endpoint with `?pages=true` param.
* `/posts/search` does not support searching pages.