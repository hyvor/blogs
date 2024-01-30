<script lang="ts">
	import { CodeBlock } from "@hyvor/design/components";

</script>

<h1 id="API-console">Console API</h1>

<p>Console API allows you to do administrative tasks of a blog. This is the same API we use internally in the Console. You can use it to automate some tasks or even build a completely new mini-console by yourself.</p>

<h2 id="calling-the-api">Calling the API</h2>

<ul>
    <li> API Basepath: <b>https://blogs.hyvor.com/api/console/v0/blog/{`{subdomain}`}</b></li>
    <li>Create a Console API Key from the Console and send it as the <code>X-API-KEY</code> header.</li>
    <li>Console API endpoints use the following HTTP methods.</li>

    <ul>
        <li><code>GET</code> - to get data, usually an array of resources</li>
        <li><code>POST</code> - to create a resource</li>
        <li><code>PATCH</code> - to partially update a resource</li>
        <li><code>PUT</code> - to completely update an resource</li>
        <li><code>DELETE</code> - to delete a resource</li>
    </ul>

    <li>Similarly to our <a href="/docs/api-data">Data API</a>, the Console API always return an object or an array of objects, in JSON format</li>
    <li>Request params can be set as JSON (recommended) or as usual request params (in query or HTTP body)</li>
    <li>In this documentation, objects, request params, and responses are written as <a href="https://www.typescriptlang.org/" rel="nofollow">Typescript</a> interfaces in order to make type declarations concise.</li>
</ul>

<h2 id="authenticating-user">Authenticating User</h2>
<p>[Coming soon]</p>
<p>Currently, the Console API is always authenticated as the owner of the blog. We will add authentication as other <a href="/docs/users">users</a> soon.</p>

<h2 id="categories">Categories</h2>

<p>The Console API has many endpoints and is categorized by what "resource" you want to access or manage. Most categories have CRUD operations but some may have more endpoints for specific tasks. These objects are defined within the Category. Also, note that Console API objects are different from <a href="/docs/api-data">Data API</a> objects.</p>
<p>Jump to each category:</p>

<ul>
    <li><a href="/docs/api-console#blog">Blog</a></li>
    <li><a href="/docs/api-console#posts">Posts &amp; Pages</a></li>
    <li><a href="/docs/api-console#tags">Tags</a></li>
    <li><a href="/docs/api-console#users">Users</a></li>
    <li><a href="/docs/api-console#media">Media</a></li>
    <li><a href="/docs/api-console#navigation">Navigation</a></li>
    <li><a href="/docs/api-console#languages">Languages</a></li>
    <li><a href="/docs/api-console#redirects">Redirects</a></li>
    <li><a href="/docs/api-console#webhooks">Webhooks</a></li>
    <li><a href="/docs/api-console#theme-files">Theme Files</a></li>
    <li><a href="/docs/api-console#data-export">Export</a></li>
    <li><a href="/docs/api-console#link-analysis">Link Analysis</a></li>
    <li><a href="/docs/api-console#route">Route</a></li>
    <li><a href="/docs/api-console#misc">Misc</a></li>
</ul>

<h3 id="blog">Blog</h3>

<p>Endpoints:</p>

<ul>
    <li><code>GET /blog</code> - Get blog data</li>
    <li><code>PATCH /blog</code> - Update blog data</li>
    <li><code>POST /blog/variant</code> - Create a blog variant</li>
    <li><code>PATCH /blog/variant</code> - Update a blog variant</li>
</ul>

<p>Objects:</p>

<ul>
    <li><a href="/docs/api-console#blog-object">Blog</a></li>
    <li><a href="/docs/api-console#blog-variant-object">BlogVariant</a></li>
</ul>

<h4 id="get-blog">Get blog data</h4>
<p><code>GET /blog</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = Blog
`} />

<h4 id="update-blog">Update blog data</h4>
<p><code>PATCH /blog</code></p>
<CodeBlock language="ts" code= {`
type Request = Partial<Blog> // except id and variants
type Response = Blog
`} />

<h4 id="create-blog-variant">Create a blog variant</h4>
<p><code>POST /blog/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    language_id: number
}
type Response = BlogVariant
`} />

<h4 id="update-blog-variant">Update a blog variant</h4>
<p><code>PATCH /blog/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    language_id: number,
    name?: string,
    description?: string
}
type Response = BlogVariant
`} />


<h3 id="posts">Posts &amp; Pages</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /posts</code> - Get posts</li>
    <li><code>GET /pages</code> - Get pages</li>
    <li><code>POST /post</code> - Create a post/page</li>
    <li><code>GET /post/{`{id}`}</code> - Get a post/page</li>
    <li><code>PATCH /post/{`{id}`}</code> - Update a post/page</li>
    <li><code>DELETE /post/{`{id}`}</code> - Delete a post/page</li>
    <li><code>POST /post/{`{id}`}/variant</code> - Create a post variant</li>
    <li><code>PATCH /post/{`{id}`}/variant</code> - Update a post variant</li>
    <li><code>DELETE /post/{`{id}`}/variant</code> - Delete a post variant</li>
    <li><code>PATCH /post/{`{id}`}/tags</code> - Update post tags</li>
    <li><code>PATCH /post/{`{id}`}/authors</code> - Update post authors</li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="/docs/api-console#post-object">Post</a></li>
    <li><a href="/docs/api-console#post-variant-object">PostVariant</a></li>
</ul>

<h4 id="get-posts">Get posts</h4>
<p>Get posts with filtering. The filter parameters are similar to the ones in the Console.</p>
<p><code>GET /posts</code></p>
<CodeBlock language="ts" code= {`
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
`} />

<h4 id="get-pages">Get pages</h4>
<p><code>GET /pages</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = Post[]
`} />

<h4 id="create-post">Create a post/page</h4>
<p>Create an empty draft post. A post variant will be created from the primary language of the blog.</p>
<p><code>POST /post</code></p>

<CodeBlock language="ts" code= {`
type Request = {
    is_page?: boolean, // default to false
}
type Response = Post
`} />

<h4 id="get-post">Get a post/page</h4>
<p><code>GET /post/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = Post
`} />

<h4 id="update-post">Update a post/page</h4>
<p><code>PATCH /post/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    is_featured?: boolean,
    featured_image_url?: string | null,
    canonical_url?: string | null,
    code_head?: string | null,
    code_foot?: string | null,
    published_at?: number | null, // unix timestamp
}
type Response = Post
`} />

<h4 id="delete-post">Delete a post/page</h4>
<p><code>DELETE /post/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h4 id="create-post-variant">Create a post variant</h4>
<p><code>POST /post/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    language_id: number
}
type Response = PostVariant
`} />

<h4 id="update-post-variant">Update a post variant</h4>
<p><code>PATCH /post/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
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
`} />

<p><code>content</code> and <code>content_unsaved</code> should be in ProseMirror JSON format. See <a href="/docs/api-console#get-prosemirror-json">Get ProseMirror JSON endpoint</a> to convert HTML to ProseMirror JSON.</p>

<h4 id="delete-post-variant">Delete a post variant</h4>
<p><code>DELETE /post/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    language_id: number
}
type Response = {}
`} />

<h4 id="update-post-tags">Update post tags</h4>
<p><code>PATCH /post/{`{id}`}/tags</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    ids: number[] // tag IDs
}
type Response = {}
`} />

<h4 id="update-post-authors">Update post authors</h4>
<p><code>PATCH /post/{`{id}`}/authors</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    ids: number[] // author (user) IDs
}
type Response = {}
`} />

<h3 id="tags">Tags</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /tags</code> - Get tags</li>
    <li><code>GET /tags/search</code> - Search tags</li>
    <li><code>POST /tag</code> - Create a tag</li>
    <li><code>PATCH /tag/{`{id}`}</code> - Update a tag</li>
    <li><code>DELETE /tag/{`{id}`}</code> - Delete a tag</li>
    <li><code>POST /tag/{`{id}`}/variant</code> - Create a tag variant</li>
    <li><code>PATCH /tag/{`{id}`}/variant</code> - Update a tag variant</li>
    <li><code>DELETE /tag/{`{id}`}/variant</code> - Delete a tag variant</li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="/docs/api-console#tag-object">Tag</a></li>
    <li><a href="/docs/api-console#tag-variant-object">TagVariant</a></li>
</ul>

<h4 id="get-tags">Get tags</h4>
<p><code>GET /tags</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    limit?: number, // default 50, max 100
    offset?: number,
}
type Response = Tag[]
`} />

<h4 id="search-tags">Search tags</h4>
<p>Searches for tags by name (primary language).</p>
<p><code>GET /tags/search</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    search: string,
}
type Response = Tag[]
`} />

<h4 id="create-tag">Create a tag</h4>
<p><code>POST /tag</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    name: string, // name for the primary language variant
}
type Response = Tag
`} />

<h4 id="update-tag">Update a tag</h4>
<p><code>PATCH /tag/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    slug?: string,
    code_head?: string | null,
    code_foot?: string | null,
}
type Response = Tag
`} />

<h4 id="delete-tag">Delete a tag</h4>
<p><code>DELETE /tag/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h4 id="create-tag-variant">Create a tag variant</h4>
<p><code>POST /tag/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    language_id: number,
}
`} />

<h4 id="update-tag-variant">Update a tag variant</h4>
<p><code>PATCH /tag/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    language_id: number,
    name?: string,
    description?: string | null,
}
`} />

<h4 id="delete-tag-variant">Delete a tag variant</h4>
<p><code>DELETE /tag/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    language_id: number,
}
`} />

<h3 id="users">Users</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /users</code> - Get users</li>
    <li><code>GET /users/search</code> - Search users</li>
    <li><code>POST /user</code> - Create a user</li>
    <li><code>POST /user/guest</code> - Create a guest user</li>
    <li><code>PATCH /user/{`{id}`}</code> - Update a user</li>
    <li><code>DELETE /user/{`{id}`}</code> - Delete a user</li>
    <li><code>POST /user/{`{id}`}/variant</code> - Create a user variant</li>
    <li><code>PATCH /user/{`{id}`}/variant</code> - Update a user variant</li>
    <li><code>DELETE /user/{`{id}`}/variant</code> - Delete a user variant</li>
    <li><code>POST /user</code> - Resend invitation email</li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="/docs/api-console#user-object">User</a></li>
    <li><a href="/docs/api-console#user-variant-object">UserVariant</a></li>
</ul>


<h4 id="get-users">Get users</h4>
<p><code>GET /users</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    offset?: number,
}
type Response = User[]
`} />

<h4 id="search-users">Search users</h4>
<p>Searches for users by name.</p>
<p><code>GET /users/search</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    search: string,
}
type Response = User[]
`} />

<h4 id="create-user">Create a user</h4>
<p><code>POST /user</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    username_or_email: string,
    role: 'owner' | 'admin' | 'editor' | 'writer' | 'contributor' | 'finance',
}
type Response = User
`} />

<h4 id="create-guest-user">Create a guest user</h4>
<p><code>POST /user/guest</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    name: string,
}
type Response = User
`} />

<h4 id="update-user">Update a user</h4>
<p><code>PATCH /user/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    hyvor_user_id?: number,
    role?: 'owner' | 'admin' | 'editor' | 'writer' | 'contributor' | 'finance',
    status: 'active' | 'blocked',
    slug: string,
    email?: string,
    website_url?: string,
    picture_url?: string,
    social_facebook?: string,
    social_twitter?: string,
    social_linkedin?: string,
    social_youtube?: string,
    social_tiktok?: string,
    social_instagram?: string,
    social_github?: string
}
type Response = User
`} />

<h4 id="delete-user">Delete a user</h4>
<p><code>DELETE /user/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h4 id="create-user-variant">Create a user variant</h4>
<p><code>POST /user/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = UserVariant
`} />

<h4 id="update-user-variant">Update a user variant</h4>
<p><code>PATCH /user/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    name?: string,
    bio?: string,
    location?: string,
}
type Response = UserVariant
`} />

<h4 id="delete-user-variant">Delete a user variant</h4>
<p><code>DELETE /user/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h4 id="resend-invite">Resend invitation email</h4>
<p><code>POST /user/{`{id}`}/resend-invite</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h3 id="media">Media</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /media</code> - Get media</li>
    <li><code>POST /media</code> - Create a media</li>
    <li><code>POST /media/from-url</code> - Create a media from URL</li>
    <li><code>DELETE /media/{`{id}`}</code> - Delete a navigation</li>
    <li><code>GET /media/unsplash/search</code> - Get media from unsplash</li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="/docs/api-console#media-object">Media</a></li>
</ul>

<h4 id="get-media">Get media</h4>
<p><code>GET /media</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    limit: number,
    offset: number,
    search?: string,
    extensions?: string[],
    type?: string
}
type Response = Media[]
`} />

<h4 id="create-media">Create a media</h4>
<p><code>POST /media</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    file: File,
    post_id: number
}
type Response = Media
`} />

<h4 id="create-media-from-url">Create a media from URL</h4>
<p><code>POST /media/from-url</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    url: string,
    post_id?: number
}
type Response = Media
`} />

<h4 id="delete-media">Delete a media</h4>
<p><code>DELETE /media/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h3 id="navigation">Navigation</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /navigations</code> - Get navigations</li>
    <li><code>PATCH /navigations/sort</code> - Update sort navigations</li>
    <li><code>POST /navigation</code> - Create a navigation</li>
    <li><code>PUT /user/{`{id}`}</code> - Update a navigation</li>
    <li><code>DELETE /navigation/{`{id}`}</code> - Delete a navigation</li>
    <li><code>POST /navigation/{`{id}`}/variant</code> - Create a navigation variant</li>
    <li><code>PUT /navigation/{`{id}`}/variant</code> - Update a navigation variant</li>
    <li><code>DELETE /navigation/{`{id}`}/variant</code> - Delete a navigation variant</li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="/docs/api-console#navigation-object">Navigation</a></li>
    <li><a href="/docs/api-console#navigation-variant-object">NavigationVariant</a></li>
</ul>

<h4 id="get-navigations">Get navigations</h4>
<p><code>GET /navigations</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = Navigation[]
`} />

<h4 id="sort-navigations">Update sort navigations</h4>
<p><code>PATCH /navigations/sort</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    ids?: number[],
}
type Response = {}
`} />

<h4 id="create-navigation">Create a navigation</h4>
<p><code>POST /navigation</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    url: string,
    name: string,
    type: 'header' | 'footer'
}
type Response = Navigation
`} />

<h4 id="update-navigation">Update a navigation</h4>
<p><code>PUT /navigation/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    url: string,
    name: string,
    type: 'header' | 'footer',
}
type Response = Navigation
`} />

<h4 id="delete-navigation">Delete a navigation</h4>
<p><code>DELETE /navigation/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h4 id="create-navigation-variant">Create a navigation variant</h4>
<p><code>POST /navigation/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    language_id: number,
    name?: string,
}
type Response = NavigationVariant
`} />

<h4 id="update-navigation-variant">Update a navigation variant</h4>
<p><code>PUT /navigation/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    name: string,
}
type Response = NavigationVariant
`} />

<h4 id="delete-navigation-variant">Delete a navigation variant</h4>
<p><code>DELETE /navigation/{`{id}`}/variant</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h3 id="language">Language</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /languages</code> - Get languages</li>
    <li><code>POST /language</code> - Create a language</li>
    <li><code>PATCH /language/{`{id}`}</code> - Update a language</li>
    <li><code>DELETE /language/{`{id}`}</code> - Delete a language</li>
</ul>

<p>Objects:</p>

<ul>
    <li><a href="/docs/api-console#language-object">Language</a></li>
</ul>

<h4 id="get-languages">Get languages</h4>
<p><code>GET /languages</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = Languages[]
`} />

<h4 id="create-language">Create a language</h4>
<p><code>POST /language</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    code: string, // max 12 chars
    name: string, // max 255 chars
    direction: 'ltr' | 'rtl',
}
type Response = Language
`} />

<h4 id="updata-language">Update a language</h4>
<p><code>PATCH /language/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    code: string, // max 12 chars
    name: string, // max 255 chars
    direction: 'ltr' | 'rtl',
}
type Response = Language
`} />

<h4 id="delete-language">Delete a language</h4>
<p><code>DELETE /language/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h3 id="redirect">Redirect</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /redirects</code> - Get redirects</li>
    <li><code>POST /redirect</code> - Create a redirect</li>
    <li><code>PUT /redirect/{`{id}`}</code> - Update a redirect</li>
    <li><code>DELETE /redirect/{`{id}`}</code> - Delete a redirect</li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="/docs/api-console#redirect-object">Redirect</a></li>
</ul>

<h4 id="get-redirects">Get redirects</h4>
<p><code>GET /redirects</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    limit?: number,
    offset?: number,
}
type Response = Redirect[]
`} />

<h4 id="create-redirect">Create a redirect</h4>
<p><code>POST /redirect</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    path: string,
    to: string,
    type: 'temporary' | 'permanent'
}
type Response = Redirect
`} />

<h4 id="updata-redirect">Update a redirect</h4>
<p><code>PUT /redirect/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    path?: string,
    to?: string,
    type?: 'temporary' | 'permanent'
}
type Response = Redirect
`} />

<h4 id="delete-redirect">Delete a redirect</h4>
<p><code>DELETE /redirect/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h3 id="webhook">Webhook</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /webhooks</code> - Get webhooks</li>
    <li><code>POST /webhook</code> - Create a webhook</li>
    <li><code>PATCH /webhook/{`{id}`}</code> - Update a webhook</li>
    <li><code>DELETE /webhook/{`{id}`}</code> - Delete a webhook</li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="/docs/api-console#webhook-object">Webhook</a></li>
</ul>

<h4 id="get-webhooks">Get webhooks</h4>
<p><code>GET /webhooks</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = Webhook[]
`} />

<h4 id="create-webhook">Create a webhook</h4>
<p><code>POST /webhook</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    url: string,
    events: 'cache.single' | 'cache.templates' | 'cache.all'[],
}
type Response = Webhook
`} />

<h4 id="updata-webhook">Update a webhook</h4>
<p><code>PATCH /webhook/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    url?: string,
    events?: 'cache.single' | 'cache.templates' | 'cache.all'[],
}
type Response = Webhook
`} />

<h4 id="delete-webhook">Delete a webhook</h4>
<p><code>DELETE /webhook/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h3 id="theme-files">Theme Files</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /theme/files</code> - Get theme files</li>
    <li><code>POST /theme/file</code> - Create a theme file</li>
    <li><code>PATCH /theme/file/{`{id}`}</code> - Update a theme file</li>
    <li><code>DELETE /theme/file/{`{id}`}</code> - Delete a theme file</li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="/docs/api-console#file-object">FileObject</a></li>
</ul>

<h4 id="get-theme-files">Get theme files</h4>
<p><code>GET /theme/files</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = FileObject[]
`} />

<h4 id="create-theme-file">Create a theme file</h4>
<p><code>POST /theme/file</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    folder: 'templates' | 'assets' | 'styles' | 'lang',
    name: string,
    content?: string,
    file: File
}
type Response = FileObject
`} />

<h4 id="updata-theme-file">Update a theme file</h4>
<p><code>PATCH /theme/file/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    name?: string,
    content?: string
}
type Response = FileObject
`} />

<h4 id="delete-theme-file">Delete a theme file</h4>
<p><code>DELETE /theme/file/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h3 id="export">Export</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /exports</code> - Get exports</li>
    <li><code>POST /export</code> - Create an export</li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="/docs/api-console#export-object">ExportObject</a></li>
</ul>

<h4 id="get-exports">Get exports</h4>
<p><code>GET /exports</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = ExportObject[]
`} />

<h4 id="create-export">Create an export</h4>
<p><code>POST /export</code></p>
<CodeBlock language="ts" code= {`
    type Request = {}
    type Response = ExportObject
    `} />

<h3 id="link-analysis">Link Analysis</h3>
<p>Endpoints:</p>
<ul>
    <li><code>POST /link-analysis/check-urls</code> - Check post variant link</li>
    <li><code>PATCH /link-analysis/ignore-link</code> - Ignore a link</li>
    <li><code>GET /link-analysis/stats</code> - Get link statistics</li>
    <li><code>GET /link-analysis/links</code> - Get links</li>
    <li><code>GET /link-analysis/checks</code> - Get checks</li>
    <li><code>POST /link-analysis/check</code> - Create a check</li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="/docs/api-console#link-object">Link</a></li>
    <li><a href="/docs/api-console#check-object">Check</a></li>
</ul>

<h4 id="check-variant-urls">Check post variant link</h4>
<p><code>POST /link-analysis/check-urls</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    post_variant_id: number,
    urls: string[]
    force?: boolean
}
type Response = LinkObject[]
`} />

<h4 id="ignore-link">Ignore a link</h4>
<p><code>PATCH /link-analysis/ignore-link</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    post_variant_id: number,
    urls: string[],
    status: boolean
}
type Response = LinkObject
`} />

<h4 id="get-link-stats">Get link statistics</h4>
<p><code>GET /link-analysis/stats</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {
    counts: number
}
`} />

<h4 id="get-links">Get links</h4>
<p><code>GET /link-analysis/links</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    type?: 'ok' | 'broken' | 'ignored' | 'redirected',
    limit?: number,
    offset?: number,
}
type Response = LinkObject[]
`} />

<h4 id="get-checks">Get checks</h4>
<p><code>GET /link-analysis/checks</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    limit?: number,
    offset?: number,
}
type Response = CheckObject[]
`} />

<h4 id="create-check">Create a check</h4>
<p><code>POST /link-analysis/check</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = CheckObject
`} />

<h3 id="route">Route</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /routes</code> - Get routes</li>
    <li><code>POST /route</code> - Create a route</li>
    <li><code>PATCH /route/{`{id}`}</code> - Update a route</li>
    <li><code>DELETE /route/{`{id}`}</code> - Delete a route</li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="/docs/api-console#route-object">Route</a></li>
</ul>

<h4 id="get-routes">Get routes</h4>
<p><code>GET /routes</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = Route[]
`} />

<h4 id="create-route">Create a route</h4>
<p><code>POST /route</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    name: string,
    match: string,
    template: string,
    post_filter?: string,
    content_type?: string
}
type Response = Route
`} />

<h4 id="update-route">Update a route</h4>
<p><code>PATCH /route/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    name: string,
    match: string,
    template: string,
    post_filter?: string,
    content_type?: string
}
type Response = Route
`} />

<h4 id="delete-route">Delete a route</h4>
<p><code>DELETE /route/{`{id}`}</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = {}
`} />

<h3 id="misc">Misc</h3>
<p>Endpoints:</p>
<ul>
    <li><code>GET /misc/themes</code> - Get all themes</li>
    <li><code>GET /misc/prosemirror/json</code> - Get prosemirror json</li>
    <li><code>DELETE /blog/cache</code> - Delete blog cache</li>
</ul>

<h4 id="get-all-themes">Get all themes</h4>
<p><code>GET /misc/themes</code></p>
<CodeBlock language="ts" code= {`
type Request = {}
type Response = Theme[]
`} />

<h4 id="get-prosemirror-json">Get prosemirror JSON from HTML</h4>
<p><code>GET /misc/prosemirror/json</code></p>
<CodeBlock language="ts" code= {`
type Request = {
  html: string,
}
type Response = {
  json: string,
}
`} />

<h4 id="delete-blog-cache">Delete blog cache</h4>
<p><code>DELETE /blog/cache</code></p>
<CodeBlock language="ts" code= {`
type Request = {
    type: 'all' | 'template' | 'paths',
    paths?: string[],
}
type Response = {}
`} />

<h2 id="objects">Objects</h2>
<h3 id="blog-object">Blog Object</h3>
<CodeBlock language="ts" code= {`  
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
`} />

<h3 id="blog-variant-object">BlogVariant Object</h3>
<CodeBlock language="ts" code= {`
interface BlogVariant {
    language_id: number,
    name: string | null,
    description: string | null,
}
`} />

<h3 id="post-object">Post Object</h3>
<CodeBlock language="ts" code= {`
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
`} />

<h3 id="post-variant-object">PostVariant Object</h3>
<CodeBlock language="ts" code= {`
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
`} />

<h3 id="tag-object">Tag Object</h3>
<CodeBlock language="ts" code= {`
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
`} />

<h3 id="tag-variant-object">TagVariant Object</h3>
<CodeBlock language="ts" code= {`
interface TagVariant {
    language_id: number,
    url: string | null,
    name: string | null,
    description: string | null,
}
`} />

<h3 id="user-object">User Object</h3>
<CodeBlock language="ts" code= {`
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
`} />

<h3 id="user-variant-object">UserVariant Object</h3>
<CodeBlock language="ts" code= {`
interface UserVariant {
    language_id: number,
    url: string,
    name: string | null,
    bio: string | null,
    location: string | null,
}
`} />

<h3 id="media-object">Media Object</h3>
<CodeBlock language="ts" code= {`
interface Media {
    id: number,
    uploaded_at: number,
    name: string,
    url: string,
    original_name: string,
    extension: string
}
`} />

<h3 id="navigation-object">Navigation Object</h3>
<CodeBlock language="ts" code= {`
interface Navigation {
    id: number;
    created_at: number;
    url: string;
    type: NavigationType,
    sort: number;
    variants: NavigationVariant[]
}
`} />

<h3 id="navigation-variant-object">NavigationVariant Object</h3>
<CodeBlock language="ts" code= {`
interface NavigationVariant {
    language_id: number,
    name: string | null
}
`} />

<h3 id="language-object">Language Object</h3>
<CodeBlock language="ts" code= {`
interface Language {
    id: number,
    code: string,
    name: string,
    is_primary: boolean
}
`} />

<h3 id="redirect-object">Redirect Object</h3>
<CodeBlock language="ts" code= {`
interface Redirect {
    id: number,
    created_at: number,
    path: string,
    to: string,
    type: 'temporary' | 'permanent'
}
`} />

<h3 id="webhook-object">Webhook Object</h3>
<CodeBlock language="ts" code= {`
interface Webhook {
    id: number,
    url: string,
    events: string[],
    secret: string,
}
`} />

<h3 id="route-object">Route Object</h3>
<CodeBlock language="ts" code= {`
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
`} />

<h3 id="file-object">File Object</h3>
<CodeBlock language="ts" code= {`
interface FileObject {
    id: number,
    name: string,
    content: string | null,
    folder: 'templates' | 'assets' | 'styles' | 'lang'
}
`} />

<h3 id="export-object">Export Object</h3>
<CodeBlock language="ts" code= {`
interface Export {
    id: number,
    createdf_at: number,
    format: 'hyvor_blogs' | 'wordpress',
    status: 'pending' | 'completed' | 'failed',
    url: string | null,
    error?: string
}
`} />

<h3 id="theme-object">Theme Object</h3>
<CodeBlock language="ts" code= {`
interface Theme {
    id: number,
    type: 'original' | 'ported',
    name: string
}
`} />

<h3 id="link-object">Link Object</h3>
<CodeBlock language="ts" code= {`
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
`} />

<h3 id="check-object">Check Object</h3>
<CodeBlock language="ts" code= {`
interface CheckObject {
    id: number,
    created_at: number,
    status: 'pending' | 'completed' | 'failed',
    error: string | null,
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
`} />



