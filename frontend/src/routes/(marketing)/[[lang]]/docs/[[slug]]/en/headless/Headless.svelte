<script lang="ts">
	import { Callout, CodeBlock } from '@hyvor/design/components';
</script>

<h1 id="headless">Headless</h1>

<p>
	You can use Hyvor Blogs purely as a <b>headless CMS</b> - writing and managing content in Hyvor Blogs,
	while rendering the blog yourself with your own framework (Next.js, SvelteKit, Astro, etc.) or even
	a native mobile app.
</p>

<Callout type="info">
	This page is an overview of the approach, not a full step-by-step tutorial. See the
	<a href="/docs/api-data">Data API reference</a> for the complete list of endpoints, objects, and query
	parameters used below.
</Callout>

<h2 id="why">Why go headless?</h2>

<ul>
	<li>
		You already have a frontend (marketing site, app, docs site) and want blog posts to live inside
		it, on the same domain and design system.
	</li>
	<li>
		You want full control over routing, layout, and rendering - beyond what <a
			href="/docs/themes-overview">themes</a
		> allow.
	</li>
	<li>You're building a mobile app or another non-web client that needs blog content as data.</li>
</ul>

<p>
	If you just want a hosted blog with a custom look, writing a <a href="/docs/themes-overview"
		>custom theme</a
	> is usually simpler than going headless - themes still run entirely on Hyvor Blogs' infrastructure
	(hosting, caching, SEO tags, redirects) for you. Headless makes sense when the blog needs to be part
	of an existing app you already own.
</p>

<h2 id="how-it-works">How it works</h2>

<ol>
	<li>
		Create a blog on Hyvor Blogs and write your posts, tags, and authors as usual in the editor.
	</li>
	<li>
		Your frontend application fetches blog data at build time or request time using the
		<a href="/docs/api-data">Data API</a>, a public, read-only JSON API.
	</li>
	<li>
		Your application renders that data into pages using your own components, routing, and styling.
	</li>
</ol>

<p>
	No API keys are required for the Data API, so you can call it directly from the browser, from a
	server, or at build time in a static site generator.
</p>

<h2 id="fetching-a-list">1. Fetching a list of posts</h2>

<p>
	Use the <code>/posts</code> endpoint on your blog's Data API base path (<code
		>{`https://blogs.hyvor.com/api/data/v0/{subdomain}`}</code
	>):
</p>

<CodeBlock
	language="ts"
	code={`
const res = await fetch(
    'https://blogs.hyvor.com/api/data/v0/example/posts?limit=10'
);
const { data: posts, pagination } = await res.json();

// posts[0] -> { id, slug, title, description, published_at, url, tags, authors, ... }
`}
/>

<p>
	Use the <a href="/docs/api-data#keys"><code>keys</code></a> param to avoid over-fetching - for a
	listing page you usually don't need the full <code>content</code> HTML:
</p>

<CodeBlock
	language="ts"
	code={`
/posts?keys=id,slug,title,description,published_at,featured_image_url,tags
`}
/>

<p>
	Pagination, filtering, and sorting all work the same way as everywhere else in the Data API - see
	<a href="/docs/api-data#page">page</a>, <a href="/docs/api-data#filter">filter</a>, and
	<a href="/docs/api-data#sort">sort</a>.
</p>

<h2 id="fetching-a-post">2. Fetching a single post</h2>

<p>Use the <code>/post</code> endpoint with either <code>slug</code> or <code>id</code>:</p>

<CodeBlock
	language="ts"
	code={`
const res = await fetch(
    'https://blogs.hyvor.com/api/data/v0/example/post?slug=hello-world'
);
const post = await res.json();

// post.content is sanitized HTML, ready to render
`}
/>

<p>
	The <code>content</code> field is HTML output by the Hyvor Blogs editor. Render it directly (e.g.
	<code>{`{@html post.content}`}</code> in Svelte, or <code>dangerouslySetInnerHTML</code> in React) -
	it's already sanitized. Images, embeds, and other media referenced in the content use absolute URLs,
	so they render correctly regardless of where you host your frontend.
</p>

<h2 id="routing">3. Routing</h2>

<p>
	Because you're not using a Hyvor Blogs theme, <b>you own the URL structure</b>. A common pattern
	is a dynamic route like <code>/blog/[slug]</code> in your app that calls
	<code>/post?slug=...</code>
	to render the page, and a listing route like <code>/blog</code> that calls
	<code>/posts</code> to build an index. Since Hyvor Blogs isn't serving these pages, features that
	depend on Hyvor Blogs generating pages for you - like automatic
	<a href="/docs/redirects">redirects</a>, <a href="/docs/routes">custom routes</a>, or theme-level
	<a href="/docs/seo">SEO</a> - don't apply; you're responsible for SEO tags, sitemaps, and redirects
	yourself in your own app.
</p>

<h2 id="build-vs-request-time">4. Build-time vs. request-time fetching</h2>

<ul>
	<li>
		<b>Static site generators</b> (Astro, Next.js static export, SvelteKit prerendering) can fetch
		all posts at build time via <code>/posts</code>, generate a static page per post, and rebuild
		when content changes (e.g. via a <a href="/docs/webhooks">webhook</a> that triggers a redeploy).
	</li>
	<li>
		<b>Server-rendered or client-rendered apps</b> can call the Data API directly on each request, since
		it requires no authentication and responses are cheap, cacheable JSON.
	</li>
</ul>

<Callout type="info">
	<p>
		Use <a href="/docs/webhooks">webhooks</a> to get notified when posts are published or updated, so
		you can invalidate a cache or trigger a rebuild instead of polling the API.
	</p>
</Callout>

<h2 id="multi-language">5. Multiple languages (optional)</h2>

<p>
	If your blog uses <a href="/docs/languages">multiple languages</a>, pass the
	<code>language</code> param on both the listing and single-post requests to get the right variant,
	and use the <code>variants</code> array on each object to build language switcher links.
</p>

<h2 id="example-stack">Example stack</h2>

<p>A minimal headless setup typically looks like:</p>

<ul>
	<li>Content: written and published in Hyvor Blogs as normal.</li>
	<li>Frontend: any framework, fetching from the <a href="/docs/api-data">Data API</a>.</li>
	<li>
		Deployment: your own hosting (Vercel, Netlify, your own server, etc.) - independent of Hyvor
		Blogs hosting.
	</li>
</ul>

<p>
	From here, the <a href="/docs/api-data">Data API reference</a> has the full list of endpoints, objects,
	and parameters (filtering, sorting, pagination, field selection) you'll need to build out listing pages,
	tag/author pages, and search.
</p>
