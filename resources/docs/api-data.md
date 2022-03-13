# Data API

Data API returns public data of the blog in JSON.

## Calling the API

There are two ways.

- Inside theme template files, you can call the Data API using Twig tags. [Designing Themes: Guide](https://www.notion.so/Designing-Themes-Guide-84a658337f9c43b6b38cc6cfda313864)  for more details. This request happens internally when the template is rendered.
- You can call `blogs.hyvor.com/api/data/v0/blog/{subdomain}` via HTTP `GET` method

All Blogs in HB are public. The Data API only returns public data of the blog. Therefore, it does not require any API keys. We cache most API responses to make subsequent requests faster. Data API does not have rate limiting.

## Endpoints

**Single-object**

- `/post` - a post/page
- `/tag`
- `/author`
- `/blog` - blog settings

**Multi-object**

- `/posts`
- `/tags`
- `/authors`
- `/languages`

## Response Format

For single-object endpoints, the response is an object. For `/post`, it is a `Post` object. (Objects are described below).

For multi-object endpoints, the response looks like this:

```json
{
	"data": [{}, {}], // array of objects
	"counts": {} // a Counts object - explained at the end
}
```

## Objects {#objects}

Data is returned in JSON objects as specified below.

<aside>
💡 All timestamps are **[Unix Timestamps](https://www.unixtimestamp.com/)**
All slugs are lowercase, and can contain `-`

</aside>

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
	"featured_image": "https://example.com/image.png",
	"canonical_url": null,
	"reading_time": 2,
	"code_head": "",
	"code_foot": "",
	
	"language": language object,

	"tags": [ tag objects ],
	"authors": [ author objects ]
}
```

| Key | Type | Description |
| --- | --- | --- |
| `id` | `integer` | A unique ID for the post |
| `created_at` | `integer` | The time the post was created (as a draft) |
| `updated_at` | `integer` | Last time the post or its meta data was updated |
| `published_at` | `integer` | Publish time of the post. |
| `is_featured` | `boolean` | Whether the post is featured. There can be multiple featured posts on a blog |
| `is_page` | `boolean` | If it is a page |
| `slug` | `string` | URL slug of the post |
| `content` | `string` | The post content in HTML. See  for more details on supported HTML tags and formatting. |
| `title` | `string` | The title of the post, a string with max length 256 |
| `description` | `string` | null \| The description (excerpt) of post, max length 350. null if not set |
| `url` | `string` | Absolute URL of the post |
| `featured_image` | `string` | null \| The absolute URL of the featured image. null if not set |
| `canonical_url` | `string` | null \| An absolute URL or null. Canonical URL is set by the author if the post was published somewhere else. |
| `reading_time` | `integer` | time in minutes. |
| `code_head` | `string` | Custom code to add before `</head>` . An empty string if nothing is set. |
| `code_foot` | `string` | Custom code to add before `</body>` . An empty string if nothing is set. |
| `language` | `object` | A Post Language object (see below)
| `tags` | `array`  | An array of Tag objects. The primary tag is the index 0 |
| `authors` | `array` | An array of Author objects. The primary author is the index 0 |

> In posts, **id** is unique. **slug + language.id** is also unique.

### Post Language Object {#post-language-object}

```json
{
	"language": a language object,
	"variants": [ post language variant objects ]
	"variants": [
		{
			"id": 1001,
			"code": "fr",
			"name": "French",
			"is_primary": false,
			"post_url": "https://subdomain.hyvorblogs.io/fr/hello-world"
		},
		{
			"id": 1002,
			"code": "es",
			"name": "Spanish",
			"is_primary": false,
			"post_url": "https://subdomain.hyvorblogs.io/es/hello-world"
		}
	]
}
```

```json
{
	"id": 1000,
	"code": "en",
	"name": "English",
	"is_primary": true,
	"variants": [
		{
			"id": 1001,
			"code": "fr",
			"name": "French",
			"is_primary": false,
			"post_url": "https://subdomain.hyvorblogs.io/fr/hello-world"
		},
		{
			"id": 1002,
			"code": "es",
			"name": "Spanish",
			"is_primary": false,
			"post_url": "https://subdomain.hyvorblogs.io/es/hello-world"
		}
	]
}
```

| Key | Type | Description |
| --- | --- | --- |
| `id` | `integer` | A unique ID for the language |
| `code` | `string` | Language code |
| `name` | `string` | Language name |
| `is_primary` | `boolean` | Whether the language is the primary language of the blog |
| `variants` | `array` | Array of **Post Language Variant** objects. These objects are similar to the Post Language objects, except it does not have the variants key, and have post URL in it. |

### Tag Object {#tag-object}

```json
{
	"id": 2000,
	"name": "Hello World",
	"slug": "hello-world",
	"url": "https://subdomain.hyvorblogs.io/tag/hello-world",
	"featured_image": "https://example.com/image.png",
	"posts_count": 20,
}
```

| Key | Type | Description |
| --- | --- | --- |
| id | integer | A unique ID for the tag |
| name | string | Name (or title) of the tag |
| slug | string | URL slug of the tag (full URL will be /tag/{slug}) |
| url | string |  |
| featured_image | string | null | The absolute URL of the featured image. null if not set |
| posts_count | integer | Number of posts of the tag |

### Author {#author-object}

<aside>
💡 "**Author**" is a "**User**" who has written at least one post.

</aside>

```json
{
	"id": 3000,
	"slug": "blogger",
	"url": "https://subdomain.hyvorblogs.io/author/blogger",
	"name": "Blogger",
	"profile_image": "https://example.com/image.png",
	"bio": "I am a blogger",
	"website_url": "https://example.com",
	"location": "France",
	"social": {
		"facebook": null,
		"twitter": null,
		"linkedin": null,
		"youtube": null,
		"instagram": null,
		"github": null,
	},
	"posts_count": 32
}
```

| Key | Type | Description |
| --- | --- | --- |
| id | integer | A unique ID for the author |
| slug | string | URL slug of the author (full URL will be /author/{slug}) |
| url | string | Full URL of the user |
| name | string | Author's name. Max length 50 |
| profile_image | string | null | The absolute URL of the author's picture. Usually, a small squared image |
| bio | string | null | Author's bio (max 256) |
| website_url | string | null | The absolute URL of the author's website |
| location | string | null | Author’s location. Max length 30 |
| social | object | An object with absolute URLs for the author's social media. |
| posts_count | integer | number of posts written by the author |

<aside>
💡 By default, `name`, `profile_image`, `cover_image` , `bio` , `website`, `country`, and social media data are synced from the author's **Hyvor Account**. However, admins of the blog can change their details at the blog level, which will stop syncing.

(It doesn't matter for the API, just mentioned it here if you wondered where the data is taken from)

</aside>

### Blog {#blog-object}

The `Blog` object is returned from the `/blog` endpoint and contains the settings of the blog.

```json
{
	"subdomain": "alex",
	"name": "My Blog", 
	"description": "This is my blog hosted on Hyvor Blogs",
	"icon": "https://example.com/icon.png",
	"featured_image": "https://example.com/icon.png",
	"lang": "en",
	"url": "https://alex.hyvorblogs.io",

	"social": {
			"facebook": "https://facebook.com/HyvorBlogs",
			"twitter": "https://twitter.com/HyvorBlogs",
			"linkedin": null,
			"youtube": null,
			"instagram": null,
	},
	
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
	]

	"code_head": "",
	"code_foot": "",

	"posts_count": 200,
}
```

| Key | Type | Description |
| --- | --- | --- |
| subdomain | string | Subdomain of the blog |
| name | string | Name/title of the blog |
| description | string | A short description of the blog (256 max) |
| icon | string | null | The absolute URL of the blog icon. Usually, a small square image. |
| featured_image | string | The absolute URL of the featured/cover image |
| lang | string | ISO 639-1 language codes. Maybe followed by a country code.
Both fr and fr-fr are valid. |
| url | string | Absolute URL of the blog. |
| social | object | see Author object |
| nav_header, nav_footer | array of objects | Navigation links for the blog header and the footer. |
| code_head, code_foot | string | Custom HTML code for before </head>, and </body> for all pages. |
| posts_count | int | Total published posts |


## Language Object {#language-object}

Language objects are returned in the `/languages` endpoint. This is similar to the Post Language Object except this does not have variants key.

```json
{
	"id": 1000,
	"code": "en",
	"name": "English",
	"is_primary": true
}
```

## Query Parameters

For Single-Object endpoints (`/post`, `/tag`, `/author`)

<aside>
💡 Either the `id` or the `slug` is required

</aside>

| Query param | Default | Description |
| --- | --- | --- |
| `id` |  | id of the object |
| `slug` |  | slug of the object |
| `language` | Default language code | (only for `/post`) language code to fetch a post of a non-default language.
| `keys` |  | GraphQL-like filtering (see below) |

For Multi-object endpoints (`/posts`, `/pages`, `/tags`, `/authors`)

| Query param | Description | Default |
| --- | --- | --- |
| `limit` | Max number of objects per page in the array | `25` |
| `page` | The page number for pagination | `1` |
| `filter` | A special notation to write filter logic (think of like the WHERE part in the SQL query - see below). | `""` |
| `sort` | How to sort the list. Supports comma-separated values (like SQL's ORDER BY - see below) |  |
| `keys` | GraphQL-like filtering (see below) |  |
| `language` | (only for `/posts`) language code to fetch posts of a non-default language. If not set, only posts of the default language are fetched. Set it to `"all"` to get posts of all languages.

### `filter` param

Example: `(published_at > 1639665890 & published_at < 1639695890) | is_featured=true`

The filter param allows you to write advanced logic like this, using comparison and logical operators. You can also group logic using parentheses.

A condition consists of three parts:

- `key`
- `operator`
- `value`

### - Operators

- `=` - equals
- `!=` - not equal
- `>` - greater than
- `<` - less than
- `>=` - greater than or equals
- `<=` - less than or equals
- `~` - SQL `LIKE`

### - Values

- `null`
- `true` or `false`
- `'string'`
    - Always wrapped with `'`
    - `'` and `"` must be escaped
- `250` - integer number

### - Logical Operators

You can use Logical Operations to combine multiple conditions.

- `&` - AND
- `|` - OR

### - Supported Keys and Operators in each endpoint

Most keys are object keys of each endpoint. But, we support additional keys like `tag.id` to allow to do something similar to SQL JOINS.

| Endpoint | Key | Supported Operators | Value | Description |
| --- | --- | --- | --- | --- |
| /posts | id | all except ~ | integer |  |
|  | published_at | all except ~ | integer | (UNIX timestamp) |
|  | updated_at | all except ~ | integer |  |
|  | is_featured | =, != | boolean |  |
|  | slug | =, !=, ~ | string |  |
|  | title | ~ | string | Ex: Hello% matches any post with a slug that starts with Hello |
|  | description | ~ , =, != | string for ~
null for =, != |  |
|  | featured_image | =, != | null | only to check if null or not |
|  | canonical_url | =, != | null |  |
|  | reading_time | all except ~ | integer |  |
|  | tag.id | =, != | integer | Matches the id of the tags of the post. |
|  | tag.slug | =, != | string | Matches the slug of the tags of the post. |
|  | author.id | =, != | integer | Similar to tag.id |
|  | author.slug | =, != | string | Similar to tag.slug |
| /tags and /authors | id | all except ~ | integer |  |
|  | slug | =, != | string |  |
|  | posts_count | all except ~ | integer |  |

Examples: 
    To select posts by Alex, call `/posts` with filter  `author.slug='alex'`
    To select posts with a title that starts with "Top", call `/posts` with filter `title~'Top%'`
    To select tags that have at least 5 posts, call `/tags` with filter `posts_count >= 5`

### `sort` param

Here's a list of supported sort values. You can combine multiple as comma-separated-values, which then will be executed in its order, similar to `ORDER BY` in SQL.

| Endpoint | Sort | Description |
| --- | --- | --- |
| /posts
(default published_at DESC) | published_at |  |
|  | created_at |  |
|  | updated_at |  |
|  | is_featured | Think of this as an integer, 1 for true and 0 for false . |
|  | title | alphabetically |
|  | reading_time |  |
| /tags and /authors | posts_count | number of posts of the tag/author |

The default sort method is `DESC`.

Here are some examples.

- `published_at` - sorted by `published_at` in descending order
- `published_at ASC` - sorted by `published_at` in ascending order
- `is_featured DESC, published_at DESC` - featured posts first, then ordered by publish time in descending order. `DESC` is optional. `is_featured, published_at` is identical with the former.

### `keys` param

The `keys` can be used to include or exclude keys from the Objects. All endpoints support the `keys` param.

If you call the `/posts` endpoint, with `keys=id,content`, call post objects will be converted to only contain those two keys.

```json
{
   "id": 1000,
	 "content": "<p></p>"
}
```

Use `!` at the start to exclude tags. For example, `keys=!content,description` will exclude `content` and `description` from the Post object.

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

### Extra: Counts Object

A counts object is included in all multi-object endpoints (`/posts`, `/authors`, `/tags`). You can use this information for pagination.

```json
{
	"total": 100,
	"total_with_filters": 50,
	"pages": 10,
	"limit": 5,
	"page": 1,
}
```

| Key | Type | Description |
| --- | --- | --- |
| total | integer | The total number of results that can be returned from this endpoint. 
Ex: If the blog has 100 posts, /posts will have total of 100. |
| total_with_filters | integer | The total number of results that  |
| pages | integer | The number of the total pagination pages based on the limit you set.
pages = total_with_filters / limit |
| limit | integer | Just returns the limit you set in the request. |
| page | integer | Just returns the page you set in the request |

## FAQ

- How can I get pages
(Pages are like posts but static and not listed in the feed - like contact us page)
    
    To get a single page, call the `/post` endpoint with the page ID or slug.
    To get multiple pages, call the `/posts` endpoint with `?pages=true` param. It will return only pages. You can other params as usual.
    
- Do you have libraries (SDK) for programming languages?