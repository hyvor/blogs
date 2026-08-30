# Console API

Console API allows you to do administrative tasks of a blog. This is the same API we use internally in the Console. You can use it to automate some tasks or even build a completely new mini-console by yourself.

<h2 id="calling-the-api">Calling the API</h2>

- API Basepath: `https://blogs.hyvor.com/api/console/v0/blog/{subdomain}`
- Create a Console API Key from the Console and send it as the `X-API-KEY` header.
- Console API endpoints use the following HTTP methods.
  - `GET` - to get data, usually an array of resources
  - `POST` - to create a resource
  - `PATCH` - to partially or completely update a resource
  - `DELETE` - to delete a resource
- Similarly to our [Data API](/docs/api-data), the Console API always return an object or an array of objects, in JSON format
- Request params can be set as JSON (recommended) or as usual request params (in query or HTTP body)
- In this documentation, objects, request params, and responses are written as <a href="https://www.typescriptlang.org/" rel="nofollow">Typescript</a> interfaces in order to make type declarations concise.

<h2 id="authenticating-user">Authenticating User</h2>

[Coming soon]

Currently, the Console API is always authenticated as the owner of the blog. We will add authentication as other [users](/docs/users) soon.

<h2 id="categories">Categories</h2>

The Console API has many endpoints and is categorized by what "resource" you want to access or manage. Most categories have CRUD operations but some may have more endpoints for specific tasks. These objects are defined within the Category. Also, note that Console API objects are different from [Data API](/docs/api-data) objects.

Jump to each category:

- [Blog](/docs/api-console#blog)
- [Posts & Pages](/docs/api-console#posts)
- [Tags](/docs/api-console#tags)
- [Users](/docs/api-console#users)
- [Media](/docs/api-console#media)
- [Navigation](/docs/api-console#navigation)
- [Languages](/docs/api-console#language)
- [Redirects](/docs/api-console#redirect)
- [Webhooks](/docs/api-console#webhook)
- [Theme Files](/docs/api-console#theme-files)
- [Export](/docs/api-console#export)
- [Link Analysis](/docs/api-console#link-analysis)
- [Route](/docs/api-console#route)
- [Misc](/docs/api-console#misc)

<h3 id="blog">Blog</h3>

Endpoints:

- `GET /blog` - Get blog data
- `PATCH /blog` - Update blog data
- `POST /blog/variant` - Create a blog variant
- `PATCH /blog/variant` - Update a blog variant

Objects:

- [Blog](/docs/api-console#blog-object)
- [BlogVariant](/docs/api-console#blog-variant-object)

<h4 id="get-blog">Get blog data</h4>

`GET /blog`

```ts
type Request = {};
type Response = Blog;
```

<h4 id="update-blog">Update blog data</h4>

`PATCH /blog`

```ts
type Request = Partial<Blog>; // except id and variants
type Response = Blog;
```

<h4 id="create-blog-variant">Create a blog variant</h4>

`POST /blog/variant`

```ts
type Request = {
	language_id: number;
};
type Response = BlogVariant;
```

<h4 id="update-blog-variant">Update a blog variant</h4>

`PATCH /blog/variant`

```ts
type Request = {
	language_id: number;
	name?: string;
	description?: string;
};
type Response = BlogVariant;
```

<h3 id="posts">Posts & Pages</h3>

Endpoints:

- `GET /posts` - Get posts
- `GET /pages` - Get pages
- `POST /post` - Create a post/page
- `GET /post/{id}` - Get a post/page
- `PATCH /post/{id}` - Update a post/page
- `DELETE /post/{id}` - Delete a post/page
- `POST /post/{id}/variant` - Create a post variant
- `PATCH /post/{id}/variant` - Update a post variant
- `POST /post/{id}/variant/publish` - Publish a post variant
- `POST /post/{id}/variant/unpublish` - Unpublish a post variant
- `DELETE /post/{id}/variant` - Delete a post variant
- `PATCH /post/{id}/tags` - Update post tags
- `PATCH /post/{id}/authors` - Update post authors

Objects:

- [Post](/docs/api-console#post-object)
- [PostVariant](/docs/api-console#post-variant-object)
- [PostListItem](/docs/api-console#post-list-item-object)

<h4 id="get-posts">Get posts</h4>

Get posts with filtering. The filter parameters are similar to the ones in the Console. Returns a lightweight [PostListItem](/docs/api-console#post-list-item-object) per post, rather than the full [Post](/docs/api-console#post-object) object - fetch `GET /post/{id}` for the full post.

`GET /posts`

```ts
type Request = {
	status?: 'featured' | 'published' | 'draft' | 'scheduled';
	author_id?: number;
	tag_id?: number;
	start_timestamp?: number; // unix timestamp
	end_timestamp?: number; // unix timestamp
	search?: string;
	language_id?: number; // defaults to the blog's primary language
	limit?: number; // default 50, max 100
	offset?: number;
};
type Response = PostListItem[];
```

<h4 id="get-pages">Get pages</h4>

Same lightweight [PostListItem](/docs/api-console#post-list-item-object) shape as `GET /posts`.

`GET /pages`

```ts
type Request = {};
type Response = PostListItem[];
```

<h4 id="create-post">Create a post/page</h4>

Create an empty draft post. A post variant will be created from the primary language of the blog.

`POST /post`

```ts
type Request = {
	is_page?: boolean; // default to false
};
type Response = Post;
```

<h4 id="get-post">Get a post/page</h4>

`GET /post/{id}`

```ts
type Request = {};
type Response = Post;
```

<h4 id="update-post">Update a post/page</h4>

`PATCH /post/{id}`

```ts
type Request = {
	is_featured?: boolean;
	featured_image_url?: string | null;
	canonical_url?: string | null;
	code_head?: string | null;
	code_foot?: string | null;
	published_at?: number | null; // unix timestamp
};
type Response = Post;
```

<h4 id="delete-post">Delete a post/page</h4>

`DELETE /post/{id}`

```ts
type Request = {};
type Response = {};
```

<h4 id="create-post-variant">Create a post variant</h4>

`POST /post/{id}/variant`

```ts
type Request = {
	language_id: number;
};
type Response = PostVariant;
```

<h4 id="update-post-variant">Update a post variant</h4>

`PATCH /post/{id}/variant`

```ts
type Request = {
	language_id: number;
	slug?: string; // max 255 chars
	content?: string | null;
	content_unsaved?: string | null;
	title?: string | null; // max 255 chars
	description?: string | null; // max 255 chars
};
type Response = PostVariant;
```

`content` and `content_unsaved` should be in ProseMirror JSON format. See [Get ProseMirror JSON endpoint](/docs/api-console#get-prosemirror-json) to convert HTML to ProseMirror JSON.

<h4 id="publish-post-variant">Publish a post variant</h4>

`POST /post/{id}/variant/publish`

```ts
type Request = {
	language_id: number;
};
type Response = PostVariant;
```

Publishes a post variant. If the variant does not have a slug, one is automatically generated from the title. If the post does not have a `published_at` time, it is set to now. Requires `posts.publish.own` scope.

<h4 id="unpublish-post-variant">Unpublish a post variant</h4>

`POST /post/{id}/variant/unpublish`

```ts
type Request = {
	language_id: number;
};
type Response = PostVariant;
```

Sets the variant status back to `draft`. Works on both published and scheduled variants. Requires `posts.publish.own` scope.

<h4 id="delete-post-variant">Delete a post variant</h4>

`DELETE /post/{id}/variant`

```ts
type Request = {
	language_id: number;
};
type Response = {};
```

<h4 id="update-post-tags">Update post tags</h4>

`PATCH /post/{id}/tags`

```ts
type Request = {
	ids: number[]; // tag IDs
};
type Response = {};
```

<h4 id="update-post-authors">Update post authors</h4>

`PATCH /post/{id}/authors`

```ts
type Request = {
	ids: number[]; // author (user) IDs
};
type Response = {};
```

<h3 id="tags">Tags</h3>

Endpoints:

- `GET /tags` - Get or search tags
- `POST /tag` - Create a tag
- `PATCH /tag/{id}` - Update a tag
- `DELETE /tag/{id}` - Delete a tag
- `POST /tag/{id}/variant` - Create a tag variant
- `PATCH /tag/{id}/variant` - Update a tag variant
- `DELETE /tag/{id}/variant` - Delete a tag variant

Objects:

- [Tag](/docs/api-console#tag-object)
- [TagVariant](/docs/api-console#tag-variant-object)

<h4 id="get-tags">Get or search tags</h4>

Lists tags, optionally searching by name (primary language).

`GET /tags`

```ts
type Request = {
	limit?: number; // default 50, max 100
	offset?: number;
	search?: string; // filters tags by name (primary language)
};
type Response = Tag[];
```

<h4 id="create-tag">Create a tag</h4>

`POST /tag`

```ts
type Request = {
	name: string; // name for the primary language variant
	is_private: boolean; // default false
};
type Response = Tag;
```

<h4 id="update-tag">Update a tag</h4>

`PATCH /tag/{id}`

```ts
type Request = {
	is_private?: boolean;
	slug?: string;
	code_head?: string | null;
	code_foot?: string | null;
};
type Response = Tag;
```

<h4 id="delete-tag">Delete a tag</h4>

`DELETE /tag/{id}`

```ts
type Request = {};
type Response = {};
```

<h4 id="create-tag-variant">Create a tag variant</h4>

`POST /tag/{id}/variant`

```ts
type Request = {
	language_id: number;
};
```

<h4 id="update-tag-variant">Update a tag variant</h4>

`PATCH /tag/{id}/variant`

```ts
type Request = {
	language_id: number;
	name?: string;
	description?: string | null;
};
```

<h4 id="delete-tag-variant">Delete a tag variant</h4>

`DELETE /tag/{id}/variant`

```ts
type Request = {
	language_id: number;
};
```

<h3 id="users">Users</h3>

Endpoints:

- `GET /users` - Get users
- `GET /users/search` - Search users
- `POST /user` - Create a user
- `POST /user/guest` - Create a guest user
- `PATCH /user/{id}` - Update a user
- `DELETE /user/{id}` - Delete a user
- `POST /user/{id}/variant` - Create a user variant
- `PATCH /user/{id}/variant` - Update a user variant
- `DELETE /user/{id}/variant` - Delete a user variant

Objects:

- [User](/docs/api-console#user-object)
- [UserVariant](/docs/api-console#user-variant-object)

<h4 id="get-users">Get users</h4>

`GET /users`

```ts
type Request = {
	offset?: number;
};
type Response = User[];
```

<h4 id="search-users">Search users</h4>

Searches for users by name.

`GET /users/search`

```ts
type Request = {
	search: string;
};
type Response = User[];
```

<h4 id="create-user">Create a user</h4>

`POST /user`

```ts
type Request = {
	username_or_email: string;
	role: 'owner' | 'admin' | 'editor' | 'writer' | 'contributor';
};
type Response = User;
```

<h4 id="create-guest-user">Create a guest user</h4>

`POST /user/guest`

```ts
type Request = {
	name: string;
};
type Response = User;
```

<h4 id="update-user">Update a user</h4>

`PATCH /user/{id}`

```ts
type Request = {
	hyvor_user_id?: number;
	role?: 'owner' | 'admin' | 'editor' | 'writer' | 'contributor';
	status: 'active' | 'blocked';
	slug: string;
	email?: string;
	website_url?: string;
	picture_url?: string;
	social_facebook?: string;
	social_twitter?: string;
	social_linkedin?: string;
	social_youtube?: string;
	social_tiktok?: string;
	social_instagram?: string;
	social_github?: string;
};
type Response = User;
```

<h4 id="delete-user">Delete a user</h4>

`DELETE /user/{id}`

```ts
type Request = {};
type Response = {};
```

<h4 id="create-user-variant">Create a user variant</h4>

`POST /user/{id}/variant`

```ts
type Request = {};
type Response = UserVariant;
```

<h4 id="update-user-variant">Update a user variant</h4>

`PATCH /user/{id}/variant`

```ts
type Request = {
	name?: string;
	bio?: string;
	location?: string;
};
type Response = UserVariant;
```

<h4 id="delete-user-variant">Delete a user variant</h4>

`DELETE /user/{id}/variant`

```ts
type Request = {};
type Response = {};
```

<h3 id="media">Media</h3>

Endpoints:

- `GET /media` - Get media
- `POST /media` - Create a media
- `POST /media/from-url` - Create a media from URL
- `DELETE /media/{id}` - Delete a navigation
- `GET /media/unsplash/search` - Get media from unsplash
- `PATCH /media` - Patch media

Objects:

- [Media](/docs/api-console#media-object)

<h4 id="get-media">Get media</h4>

`GET /media`

```ts
type Request = {
	limit: number;
	offset: number;
	search?: string;
	extensions?: string[];
	type?: string;
};
type Response = Media[];
```

<h4 id="create-media">Create a media</h4>

`POST /media`

```ts
type Request = {
	file: File;
	post_id: number;
};
type Response = Media;
```

<h4 id="create-media-from-url">Create a media from URL</h4>

`POST /media/from-url`

```ts
type Request = {
	url: string;
	post_id?: number;
};
type Response = Media;
```

<h4 id="delete-media">Delete a media</h4>

`DELETE /media/{id}`

```ts
type Request = {};
type Response = {};
```

<h4 id="update-media">Patch a media</h4>

`PATCH /media/{id}`

```ts
type Request = Partial<Media>;
type Response = Media;
```

<h3 id="navigation">Navigation</h3>

Endpoints:

- `GET /navigations` - Get navigations
- `PATCH /navigations/sort` - Update sort navigations
- `POST /navigation` - Create a navigation
- `PATCH /navigation/{id}` - Update a navigation
- `DELETE /navigation/{id}` - Delete a navigation
- `POST /navigation/{id}/variant` - Create a navigation variant
- `PATCH /navigation/{id}/variant` - Update a navigation variant
- `DELETE /navigation/{id}/variant` - Delete a navigation variant

Objects:

- [Navigation](/docs/api-console#navigation-object)
- [NavigationVariant](/docs/api-console#navigation-variant-object)

<h4 id="get-navigations">Get navigations</h4>

`GET /navigations`

```ts
type Request = {};
type Response = Navigation[];
```

<h4 id="sort-navigations">Update sort navigations</h4>

`PATCH /navigations/sort`

```ts
type Request = {
	ids?: number[];
};
type Response = {};
```

<h4 id="create-navigation">Create a navigation</h4>

`POST /navigation`

```ts
type Request = {
	url: string;
	name: string;
	type: 'header' | 'footer';
};
type Response = Navigation;
```

<h4 id="update-navigation">Update a navigation</h4>

`PATCH /navigation/{id}`

```ts
type Request = {
	url: string;
	type: 'header' | 'footer';
};
type Response = Navigation;
```

<h4 id="delete-navigation">Delete a navigation</h4>

`DELETE /navigation/{id}`

```ts
type Request = {};
type Response = {};
```

<h4 id="create-navigation-variant">Create a navigation variant</h4>

`POST /navigation/{id}/variant`

```ts
type Request = {
	language_id: number;
	name?: string;
};
type Response = NavigationVariant;
```

<h4 id="update-navigation-variant">Update a navigation variant</h4>

`PATCH /navigation/{id}/variant`

```ts
type Request = {
	language_id: number;
	name: string;
};
type Response = NavigationVariant;
```

<h4 id="delete-navigation-variant">Delete a navigation variant</h4>

`DELETE /navigation/{id}/variant`

```ts
type Request = {
	language_id: number;
};
type Response = {};
```

<h3 id="language">Language</h3>

Endpoints:

- `GET /languages` - Get languages
- `POST /language` - Create a language
- `PATCH /language/{id}` - Update a language
- `DELETE /language/{id}` - Delete a language

Objects:

- [Language](/docs/api-console#language-object)

<h4 id="get-languages">Get languages</h4>

`GET /languages`

```ts
type Request = {};
type Response = Languages[];
```

<h4 id="create-language">Create a language</h4>

`POST /language`

```ts
type Request = {
	code: string; // max 12 chars
	name: string; // max 255 chars
	direction: 'ltr' | 'rtl';
};
type Response = Language;
```

<h4 id="updata-language">Update a language</h4>

`PATCH /language/{id}`

```ts
type Request = {
	code: string; // max 12 chars
	name: string; // max 255 chars
	direction: 'ltr' | 'rtl';
};
type Response = Language;
```

<h4 id="delete-language">Delete a language</h4>

`DELETE /language/{id}`

```ts
type Request = {};
type Response = {};
```

<h3 id="redirect">Redirect</h3>

Endpoints:

- `GET /redirects` - Get redirects
- `POST /redirect` - Create a redirect
- `PATCH /redirect/{id}` - Update a redirect
- `DELETE /redirect/{id}` - Delete a redirect

Objects:

- [Redirect](/docs/api-console#redirect-object)

<h4 id="get-redirects">Get redirects</h4>

`GET /redirects`

```ts
type Request = {
	search?: string;
	limit?: number;
	offset?: number;
};
type Response = Redirect[];
```

<h4 id="create-redirect">Create a redirect</h4>

`POST /redirect`

```ts
type Request = {
	dynamic: boolean;
	path: string;
	to: string;
	type: 'temporary' | 'permanent';
};
type Response = Redirect;
```

<h4 id="update-redirect">Update a redirect</h4>

`PATCH /redirect/{id}`

```ts
type Request = {
	path?: string;
	to?: string;
	type?: 'temporary' | 'permanent';
};
type Response = Redirect;
```

<h4 id="delete-redirect">Delete a redirect</h4>

`DELETE /redirect/{id}`

```ts
type Request = {};
type Response = {};
```

<h3 id="webhook">Webhook</h3>

Endpoints:

- `GET /webhooks` - Get webhooks
- `POST /webhook` - Create a webhook
- `PATCH /webhook/{id}` - Update a webhook
- `DELETE /webhook/{id}` - Delete a webhook

Objects:

- [Webhook](/docs/api-console#webhook-object)

<h4 id="get-webhooks">Get webhooks</h4>

`GET /webhooks`

```ts
type Request = {};
type Response = Webhook[];
```

<h4 id="create-webhook">Create a webhook</h4>

`POST /webhook`

```ts
type Request = {
	url: string;
	events: 'cache.single' | 'cache.templates' | 'cache.all'[];
};
type Response = Webhook;
```

<h4 id="updata-webhook">Update a webhook</h4>

`PATCH /webhook/{id}`

```ts
type Request = {
	url?: string;
	events?: 'cache.single' | 'cache.templates' | 'cache.all'[];
};
type Response = Webhook;
```

<h4 id="delete-webhook">Delete a webhook</h4>

`DELETE /webhook/{id}`

```ts
type Request = {};
type Response = {};
```

<h3 id="theme-files">Theme Files</h3>

Endpoints:

- `GET /theme/files` - Get theme files
- `POST /theme/file` - Create a theme file
- `PATCH /theme/file/{id}` - Update a theme file
- `DELETE /theme/file/{id}` - Delete a theme file

Objects:

- [FileObject](/docs/api-console#file-object)

<h4 id="get-theme-files">Get theme files</h4>

`GET /theme/files`

```ts
type Request = {};
type Response = FileObject[];
```

<h4 id="create-theme-file">Create a theme file</h4>

`POST /theme/file`

```ts
type Request = {
	folder: 'templates' | 'assets' | 'styles' | 'lang';
	name: string;
	content?: string;
	file: File;
};
type Response = FileObject;
```

<h4 id="updata-theme-file">Update a theme file</h4>

`PATCH /theme/file/{id}`

```ts
type Request = {
	name?: string;
	content?: string;
};
type Response = FileObject;
```

<h4 id="delete-theme-file">Delete a theme file</h4>

`DELETE /theme/file/{id}`

```ts
type Request = {};
type Response = {};
```

<h3 id="export">Export</h3>

Endpoints:

- `GET /exports` - Get exports
- `POST /export` - Create an export

Objects:

- [ExportObject](/docs/api-console#export-object)

<h4 id="get-exports">Get exports</h4>

`GET /exports`

```ts
type Request = {};
type Response = ExportObject[];
```

<h4 id="create-export">Create an export</h4>

`POST /export`

```ts
type Request = {};
type Response = ExportObject;
```

<h3 id="link-analysis">Link Analysis</h3>

Endpoints:

- `POST /link-analysis/check-urls` - Check post variant link
- `PATCH /link-analysis/ignore-link` - Ignore a link
- `GET /link-analysis/stats` - Get link statistics
- `GET /link-analysis/links` - Get links
- `GET /link-analysis/checks` - Get checks
- `POST /link-analysis/check` - Create a check

Objects:

- [Link](/docs/api-console#link-object)
- [Check](/docs/api-console#check-object)

<h4 id="check-variant-urls">Check post variant link</h4>

`POST /link-analysis/check-urls`

```ts
type Request = {
	post_variant_id: number;
	urls: string[];
	force?: boolean;
};
type Response = LinkObject[];
```

<h4 id="ignore-link">Ignore a link</h4>

`PATCH /link-analysis/ignore-link`

```ts
type Request = {
	post_variant_id: number;
	urls: string[];
	status: boolean;
};
type Response = LinkObject;
```

<h4 id="get-link-stats">Get link statistics</h4>

`GET /link-analysis/stats`

```ts
type Request = {};
type Response = {
	counts: number;
};
```

<h4 id="get-links">Get links</h4>

`GET /link-analysis/links`

```ts
type Request = {
	type?: 'ok' | 'broken' | 'ignored' | 'redirected';
	limit?: number;
	offset?: number;
};
type Response = LinkObject[];
```

<h4 id="get-checks">Get checks</h4>

`GET /link-analysis/checks`

```ts
type Request = {
	limit?: number;
	offset?: number;
};
type Response = CheckObject[];
```

<h4 id="create-check">Create a check</h4>

`POST /link-analysis/check`

```ts
type Request = {};
type Response = CheckObject;
```

<h3 id="route">Route</h3>

Endpoints:

- `GET /routes` - Get routes
- `POST /route` - Create a route
- `PATCH /route/{id}` - Update a route
- `DELETE /route/{id}` - Delete a route

Objects:

- [Route](/docs/api-console#route-object)

<h4 id="get-routes">Get routes</h4>

`GET /routes`

```ts
type Request = {};
type Response = Route[];
```

<h4 id="create-route">Create a route</h4>

`POST /route`

```ts
type Request = {
	name: string;
	match: string;
	template: string;
	post_filter?: string;
	content_type?: string;
};
type Response = Route;
```

<h4 id="update-route">Update a route</h4>

`PATCH /route/{id}`

```ts
type Request = {
	name: string;
	match: string;
	template: string;
	post_filter?: string;
	content_type?: string;
};
type Response = Route;
```

<h4 id="delete-route">Delete a route</h4>

`DELETE /route/{id}`

```ts
type Request = {};
type Response = {};
```

<h3 id="misc">Misc</h3>

Endpoints:

- `GET /misc/themes` - Get all themes
- `GET /misc/prosemirror/json` - Get prosemirror json
- `DELETE /blog/cache` - Delete blog cache
- `DELETE /blog` - Delete the blog

<h4 id="get-all-themes">Get all themes</h4>

`GET /misc/themes`

```ts
type Request = {};
type Response = Theme[];
```

<h4 id="get-prosemirror-json">Get prosemirror JSON from HTML</h4>

`GET /misc/prosemirror/json`

```ts
type Request = {
	html: string;
};
type Response = {
	json: string;
};
```

<h4 id="delete-blog-cache">Delete blog cache</h4>

`DELETE /blog/cache`

```ts
type Request = {
	type: 'all' | 'template' | 'paths';
	paths?: string[];
};
type Response = {};
```

<h4 id="delete-blog">Delete the blog</h4>

Soft-deletes the blog. The blog and its data are permanently deleted 30 days later. Requires the `blog.delete` scope.

`DELETE /blog`

```ts
type Request = {};
type Response = {};
```

<h2 id="objects">Objects</h2>
<h3 id="blog-object">Blog Object</h3>

```ts
interface Blog {
	id: number;
	created_at: number;
	is_blocked: boolean;
	subdomain: string;
	type: 'default' | 'dev';
	hosting_at: 'subdomain' | 'domain' | 'self';
	hosting_domain: string | null;
	hosting_url: string | null;

	embeddable: boolean;
	embedding_domains: string | null;

	logo_url: string | null;
	cover_url: string | null;

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
	seo_robots_txt: string | null;
	seo_external_links_follow: 'follow' | 'nofollow';
	comments_code: string | null;
	newsletter_code: string | null;

	color_modes: 'light' | 'dark' | 'both';
	color_mode_default: 'light' | 'dark' | 'os';

	syntax_on: boolean;
	syntax_line_numbers: boolean;
	syntax_theme: string | null;

	flashload: boolean;
	variants: BlogVariant[];
}
```

<h3 id="blog-variant-object">BlogVariant Object</h3>

```ts
interface BlogVariant {
	language_id: number;
	name: string | null;
	description: string | null;
}
```

<h3 id="post-object">Post Object</h3>

```ts
interface Post {
	id: number;
	preview_id: string;
	created_at: number;
	updated_at: number;
	published_at: number | null;

	is_featured: boolean;
	is_page: boolean;

	featured_image_url: string | null;
	canonical_url: string | null;
	code_head: string | null;
	code_foot: string | null;

	variant_statuses: {
		id: number;
		language_id: number;
		status: 'draft' | 'published' | 'scheduled';
	}[];

	tags: Tag[];
	authors: User[];
}
```

`variant_statuses` only tells you which languages a post has and their status. Fetch `GET /post/{id}?variant_language_code=...` to get the full [PostVariant](/docs/api-console#post-variant-object) object (content, title, SEO fields, etc.) for a single language.

<h3 id="post-variant-object">PostVariant Object</h3>

```ts
interface PostVariant {
	language_id: number;

	slug: string | null;
	status: 'draft' | 'published' | 'scheduled';
	url: string;

	content: string | null;
	content_unsaved: string | null;
	title: string | null;
	description: string | null;
}
```

<h3 id="post-list-item-object">PostListItem Object</h3>

Returned by `GET /posts` and `GET /pages`. A lightweight per-post summary: `slug`, `url`, `title`, and `link_analysis` reflect the variant of the requested (or blog's primary) language, and `tags`/`authors` are just their primary-language names. Fetch `GET /post/{id}` for the full [Post](/docs/api-console#post-object) object, including tags and authors.

```ts
interface PostListItem {
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

	variant_statuses: {
		language_id: number;
		status: 'draft' | 'published' | 'scheduled';
	}[];

	tags: string[]; // tag names, primary language
	authors: string[]; // author names, primary language
}
```

<h3 id="tag-object">Tag Object</h3>

```ts
interface Tag {
	id: number;
	created_at: number;
	updated_at: number;
	is_private: boolean;
	slug: string;
	posts_count: number;
	code_head: string | null;
	code_foot: string | null;

	variants: TagVariant[];
}
```

<h3 id="tag-variant-object">TagVariant Object</h3>

```ts
interface TagVariant {
	language_id: number;
	url: string | null;
	name: string | null;
	description: string | null;
}
```

<h3 id="user-object">User Object</h3>

```ts
interface User {
	id: number;
	created_at: number;
	updated_at: number;

	hyvor_user_id: number | null;

	status: 'invited' | 'active' | 'blocked';
	role: 'owner' | 'admin' | 'editor' | 'writer' | 'contributor';
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
}
```

<h3 id="user-variant-object">UserVariant Object</h3>

```ts
interface UserVariant {
	language_id: number;
	url: string;
	name: string | null;
	bio: string | null;
	location: string | null;
}
```

<h3 id="media-object">Media Object</h3>

```ts
interface Media {
	id: number;
	uploaded_at: number;
	name: string;
	url: string;
	original_name: string;
	extension: string;
}
```

<h3 id="navigation-object">Navigation Object</h3>

```ts
interface Navigation {
	id: number;
	created_at: number;
	url: string;
	type: NavigationType;
	sort: number;
	variants: NavigationVariant[];
}
```

<h3 id="navigation-variant-object">NavigationVariant Object</h3>

```ts
interface NavigationVariant {
	language_id: number;
	name: string | null;
}
```

<h3 id="language-object">Language Object</h3>

```ts
interface Language {
	id: number;
	code: string;
	name: string;
	is_primary: boolean;
}
```

<h3 id="redirect-object">Redirect Object</h3>

```ts
interface Redirect {
	id: number;
	created_at: number;
	path: string;
	to: string;
	type: 'temporary' | 'permanent';
}
```

<h3 id="webhook-object">Webhook Object</h3>

```ts
interface Webhook {
	id: number;
	url: string;
	events: string[];
	secret: string;
}
```

<h3 id="route-object">Route Object</h3>

```ts
interface Route {
	id: number;
	created_at: number;
	name: string;
	match: string;
	template: string;
	posts_filter: string | null;
	content_type: string | null;
	is_enabled: boolean;
}
```

<h3 id="file-object">File Object</h3>

```ts
interface FileObject {
	id: number;
	name: string;
	content: string | null;
	folder: 'templates' | 'assets' | 'styles' | 'lang';
}
```

<h3 id="export-object">Export Object</h3>

```ts
interface Export {
	id: number;
	createdf_at: number;
	format: 'hyvor_blogs' | 'wordpress';
	status: 'pending' | 'completed' | 'failed';
	url: string | null;
	error?: string;
}
```

<h3 id="theme-object">Theme Object</h3>

```ts
interface Theme {
	id: number;
	type: 'original' | 'ported';
	name: string;
}
```

<h3 id="link-object">Link Object</h3>

```ts
interface LinkObject {
	id: number;
	url: string;
	full_url: string;
	status_code: number;
	status_type: 'ok' | 'broken' | 'redirect' | 'ignored';
	ignored: boolean;
	post_id: number;
	post_variant_id: number;
	post_variant_language_id: number;
	post_variant_title: string;
}
```

<h3 id="check-object">Check Object</h3>

```ts
interface CheckObject {
	id: number;
	created_at: number;
	status: 'pending' | 'completed' | 'failed';
	error: string | null;
	post_count: number;
	post_variants_count: number;
	page_count: number;
	page_variants_count: number;
	links_total_count: number;
	links_ok_count: number;
	links_broken_count: number;
	links_redirect_count: number;
	links_ignored_count: number;
}
```
