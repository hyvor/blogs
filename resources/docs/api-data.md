# Data API

Data API returns **public data** of the blog in JSON format. It does not require any API keys. There are two ways to call the API:

- Via Public Endpoint: `https://blogs.hyvor.com/api/data/v0/blog/{subdomain}` (HTTP GET method)
- Via template files. See [themes documentation](themes-templates#fetch-data)

## Endpoints

**Single-object**

- `/post` - a post/page
- `/tag`
- `/author`
- `/blog` - blog settings

**Multi-object**

- `/posts`
- `/posts/search`
- `/tags`
- `/authors`

## Response Format

For single-object endpoints, the response is an object. For `/post`, it is a `Post` object (See Below for object definitions).

```json
// A Post Object
{
	"id": 1000,
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

## Objects {#objects}

Data is returned in JSON objects as specified below.

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
	"featured_image": "https://example.com/image.png",
	"canonical_url": null,
	"reading_time": 2,
	"code_head": "",
	"code_foot": "",
	
	"language": language object,
	"variants": [ variant objects ],

	"tags": [ tag objects ],
	"authors": [ author objects ]
}
```

| Key | Type | Description |
| --- | --- | --- |
| `id` | `integer` | A unique ID for the post |
| `created_at` | `integer` | The time the post was created (as a draft) |
| `updated_at` | `integer` | The time the post or its meta data was updated |
| `published_at` | `integer` | Publish time of the post. |
| `is_featured` | `boolean` | Whether the post is featured. There can be multiple featured posts on a blog |
| `is_page` | `boolean` | Whether it is a page. See [Posts & Pages](posts-pages) |
| `slug` | `string` | The URL slug of the post |
| `url` | `string` | The absolute URL of the post, generated based on where the blog is [hosted](hosting) |
| `content` | `string` | The post content in HTML. See [Content & The Editor](content-editor) to see supported HTML tags |
| `title` | `string` | The title of the post, max length 256 |
| `description` | `string|null` | The description (excerpt) of post, max length 350, null if not set |
| `featured_image` | `string|null` | The absolute URL of the featured image. null if not set |
| `canonical_url` | `string|null` | An absolute URL or null. Canonical URL is set by the author if the post was published somewhere else. |
| `reading_time` | `integer` | Reading time in minutes. |
| `code_head` | `string` | [Custom code](custom-code) to add before `</head>` . An empty string if nothing is set. |
| `code_foot` | `string` | [Custom code](custom-code) to add before `</body>` . An empty string if nothing is set. |
| `language` | `object` | A [Language object](#language-object)
| `variants` | `array` | An array of [Variant objects](#variant-object). |
| `tags` | `array`  | An array of [Tag objects](#tag-object). The primary tag is the index 0 |
| `authors` | `array` | An array of [Author objects](#author-object). The primary author is the index 0 |

> In posts, **id** attribute is globally unique within Hyvor Blogs. The **slug** attribute is unique within the blog.

### Tag Object {#tag-object}

```json
{
	"id": 2000,
	"name": "Hello World",
	"slug": "hello-world",
	"url": "https://subdomain.hyvorblogs.io/tag/hello-world",
	"featured_image": "https://example.com/image.png",
	"posts_count": 20,

	"language": language object,
	"variants": [ variant objects ],
}
```

| Key | Type | Description |
| --- | --- | --- |
| `id` | `integer` | A unique ID for the tag |
| `name` | `string` | Name (or title) of the tag |
| `slug` | `string` | URL slug of the tag (full URL will be /tag/{slug}) |
| `url` | `string` |  |
| `featured_image` | `string|null` | The absolute URL of the featured image. null if not set |
| `posts_count` | `integer` | Number of posts of the tag |
| `language` | `object` | A [Language object](#language-object)
| `variants` | `array` | An array of [Variant objects](#variant-object) |

### Author Object {#author-object}

> Author is a [user](users) who has written at least one post

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
	"posts_count": 32,

	"language": language object,
	"variants": [ variant objects ],
}
```

| Key | Type | Description |
| --- | --- | --- |
| `id` | `integer` | A unique ID for the author |
| `slug` | `string` | URL slug of the author (full URL will be /author/{slug}) |
| `url` | `string` | Full URL of the user |
| `name` | `string` | Author's name. Max length 50 |
| `profile_image` | `string|null` | The absolute URL of the author's picture. Usually, a small squared image |
| `bio` | `string|null` | Author's bio (max 256) |
| `website_url` | `string|null` | The absolute URL of the author's website |
| `location` | `string|null` | Author’s location. Max length 30 |
| `social` | `object` | An object with absolute URLs for the author's social media. |
| `posts_count` | `integer` | number of posts written by the author |
| `language` | `object` | A [Language object](#language-object)
| `variants` | `array` | An array of [Variant objects](#variant-object). |

### Blog Object {#blog-object}

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
	],

	"languages": [ language objects ],

	"code_head": "",
	"code_foot": "",

	"posts_count": 200,
}
```

| Key | Type | Description |
| --- | --- | --- |
| `subdomain` | `string` | Subdomain of the blog |
| `name` | `string` | Name/title of the blog |
| `description` | `string` | A short description of the blog (256 max) |
| `icon` | `string` | null | The absolute URL of the blog icon. Usually, a small square image. |
| `featured_image` | `string` | The absolute URL of the featured/cover image |
| `url` | `string` | Absolute URL of the blog. |
| `social` | `object` | see Author object |
| `nav_header`, `nav_footer` | `array of objects` | Navigation links for the blog header and the footer. |
| `languages`| `array of objects` | All available languages of the blog
| `code_head`, `code_foot` | string | Custom HTML code for before </head>, and </body> for all pages. |
| `posts_count` | int | Total published posts |

### Language Object {#language-object}

```json
{
	"id": 1000,
	"code": "en",
	"name": "English",
	"is_primary": true
}
```

| Key | Type | Description |
| --- | --- | --- |
| `id` | `integer` | A unique ID for the language |
| `code` | `string` | Language code |
| `name` | `string` | Language name |
| `is_primary` | `boolean` | Whether the language is the primary language of the blog |

### Variant Object {#variant-object}

A variant object contains data of a language variant of a post, tag, or an author. `language` in this object is a [Language Object](#language-object).

```json
{
	"language": {
		"id": 1001,
		"code": "fr",
		"name": "French",
		"is_primary": false
	},
	"url": "https://subdomain.hyvorblogs.io/fr/hello-world"
}
```

## Query Parameters

#### For Single-Object endpoints

(`/post`, `/tag`, `/author`)


| Query param | Default | Description |
| --- | --- | --- |
| `id` |  | id of the object |
| `slug` |  | slug of the object |
| `language` | Primary language code | Language code to localize strings in objects. If not provided, the primary language will be used.
| `keys` |  | GraphQL-like filtering (See [keys](#keys)) |

> Either the `id` or the `slug` is required for those endpoints.

`/blog` endpoint only takes `language` and `keys` as an input.

#### For Multi-object endpoints 

(`/posts`, `/pages`, `/tags`, `/authors`)

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

### `keys` param {#keys}

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