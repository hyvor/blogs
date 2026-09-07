<script lang="ts">
	import { Callout } from '@hyvor/design/components';
</script>

<h1 id="headless">Headless</h1>

You can use Hyvor Blogs purely as a **headless CMS** - writing and managing content in Hyvor Blogs, while rendering the blog yourself with your own framework (Next.js, SvelteKit, Astro, etc.) or even a native mobile app.

<Callout type="info">
	This page is an overview of the approach, not a full step-by-step tutorial. See the
	<a href="/docs/api-data">Data API reference</a> for the complete list of endpoints, objects, and query
	parameters used below.
</Callout>

<h2 id="why">Why go headless?</h2>

- You already have a frontend (marketing site, app, docs site) and want blog posts to live inside it, on the same domain and design system.
- You want full control over routing, layout, and rendering - beyond what [themes](/docs/themes-overview) allow.
- You're building a mobile app or another non-web client that needs blog content as data.

If you just want a hosted blog with a custom look, writing a [custom theme](/docs/themes-overview) is usually simpler than going headless - themes still run entirely on Hyvor Blogs' infrastructure (hosting, caching, SEO tags, redirects) for you. Headless makes sense when the blog needs to be part of an existing app you already own.

<h2 id="how-it-works">How it works</h2>

1. Create a blog on Hyvor Blogs and write your posts, tags, and authors as usual in the editor.
2. Your frontend application fetches blog data at build time or request time using the [Data API](/docs/api-data), a public, read-only JSON API.
3. Your application renders that data into pages using your own components, routing, and styling.

No API keys are required for the Data API, so you can call it directly from the browser, from a server, or at build time in a static site generator.

<h2 id="fetching-a-list">1. Fetching a list of posts</h2>

Use the `/posts` endpoint on your blog's Data API base path (`https://blogs.hyvor.com/api/data/v0/{subdomain}`):

```ts
const res = await fetch('https://blogs.hyvor.com/api/data/v0/example/posts?limit=10');
const { data: posts, pagination } = await res.json();

// posts[0] -> { id, slug, title, description, published_at, url, tags, authors, ... }
```

Use the [`keys`](/docs/api-data#keys) param to avoid over-fetching - for a listing page you usually don't need the full `content` HTML:

```ts
/posts?keys=id,slug,title,description,published_at,featured_image_url,tags
```

Pagination, filtering, and sorting all work the same way as everywhere else in the Data API - see [page](/docs/api-data#page), [filter](/docs/api-data#filter), and [sort](/docs/api-data#sort).

<h2 id="fetching-a-post">2. Fetching a single post</h2>

Use the `/post` endpoint with either `slug` or `id`:

```ts
const res = await fetch('https://blogs.hyvor.com/api/data/v0/example/post?slug=hello-world');
const post = await res.json();

// post.content is sanitized HTML, ready to render
```

The `content` field is HTML output by the Hyvor Blogs editor. Render it directly (e.g. `{@html post.content}` in Svelte, or `dangerouslySetInnerHTML` in React) - it's already sanitized. Images, embeds, and other media referenced in the content use absolute URLs, so they render correctly regardless of where you host your frontend.

<h2 id="routing">3. Routing</h2>

Because you're not using a Hyvor Blogs theme, **you own the URL structure**. A common pattern is a dynamic route like `/blog/[slug]` in your app that calls `/post?slug=...` to render the page, and a listing route like `/blog` that calls `/posts` to build an index. Since Hyvor Blogs isn't serving these pages, features that depend on Hyvor Blogs generating pages for you - like automatic [redirects](/docs/redirects), [custom routes](/docs/routes), or theme-level [SEO](/docs/seo) - don't apply; you're responsible for SEO tags, sitemaps, and redirects yourself in your own app.

<h2 id="build-vs-request-time">4. Build-time vs. request-time fetching</h2>

- **Static site generators** (Astro, Next.js static export, SvelteKit prerendering) can fetch all posts at build time via `/posts`, generate a static page per post, and rebuild when content changes (e.g. via a [webhook](/docs/webhooks) that triggers a redeploy).
- **Server-rendered or client-rendered apps** can call the Data API directly on each request, since it requires no authentication and responses are cheap, cacheable JSON.

<Callout type="info">
	<p>
		Use <a href="/docs/webhooks">webhooks</a> to get notified when posts are published or updated, so
		you can invalidate a cache or trigger a rebuild instead of polling the API.
	</p>
</Callout>

<h2 id="multi-language">5. Multiple languages (optional)</h2>

If your blog uses [multiple languages](/docs/languages), pass the `language` param on both the listing and single-post requests to get the right variant, and use the `variants` array on each object to build language switcher links.

<h2 id="example-stack">Example stack</h2>

A minimal headless setup typically looks like:

- Content: written and published in Hyvor Blogs as normal.
- Frontend: any framework, fetching from the [Data API](/docs/api-data).
- Deployment: your own hosting (Vercel, Netlify, your own server, etc.) - independent of Hyvor Blogs hosting.

From here, the [Data API reference](/docs/api-data) has the full list of endpoints, objects, and parameters (filtering, sorting, pagination, field selection) you'll need to build out listing pages, tag/author pages, and search.
