<script lang="ts">
	import { Table, TableRow } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# SEO

Hyvor Blogs handles technical SEO for you, allowing you to focus on what matters the most -
writing. Some of the SEO features can be configured in <strong>Console &rarr; Settings &rarr; SEO</strong>.

<h2 id="meta">Meta Tags</h2>

Hyvor Blogs automatically meta tags to your blog. They help search engines to understand your blog
better and social media sites to display your blog better.

<h3 id="basic">Basic Meta Tags</h3>

These are the basic meta tags that are added to all pages in your blog.

```
<title>My Blog</title>
<meta name="description" content="My Blog Description" />
<link rel="canonical" href="https://myblog.hyvorblogs.io" />
```

<h3 id="language-variants">Language Variants</h3>

If you have [set up multiple languages](/docs/languages), Hyvor Blogs will
automatically add `hreflang` tags to index and post pages.

```
<link rel="alternate" href="https://myblog.hyvorblogs.io/fr" hreflang="fr" />
<link rel="alternate" href="https://myblog.hyvorblogs.io/es" hreflang="es" />
```

<h3 id="facebook-twitter-tags">Facebook and Twitter Tags</h3>

These tags help social media sites to generate rich previews of your blog and posts.

```
<!-- FACEBOOK (OG) -->
<meta property="og:site_name" />
<meta property="og:type" />
<meta property="og:title" />
<meta property="og:locale" />
<meta property="og:description" />
<meta property="og:url" />
<meta property="og:image" />
<!-- For Posts -->
<meta property="article:published_time" />
<meta property="article:modified_time" />
<meta property="article:author" />  <!-- Authors -->
<meta property="article:author" />
<meta property="article:section" />  <!-- Tags -->
<meta property="article:section" />

<!-- TWITTER -->
<meta name="twitter:card" />
<meta name="twitter:title" />
<meta name="twitter:description" />
<meta name="twitter:url" />
<meta name="twitter:image" />
<meta name="twitter:site" /> <!-- only if Twitter URL is set in blog settings -->
<meta name="twitter:creator" /> <!-- only if Twitter URL is set for the primary author -->
```

<h2 id="rich-schema">Rich Schema</h2>

A rich schema of <a href="https://developers.google.com/search/docs/appearance/structured-data/article" target="_blank" rel="nofollow">BlogPosting</a> is added to all posts. This helps search engines to understand your posts better and display them
in a better way in search results.

```ts
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "How to start a blog",
    "datePublished": "2024-01-01T00:00:00Z",
    "dateModified": "2024-01-01T00:00:00Z",
    "author": [
        {
            "type": "@Person",
            "name": "John Doe",
            "url": "https://blog.hyvorblogs.io/author/john-doe"
        }
    ],
    "image": [
        "https://blog.hyvorblogs.io/media/how-to-start-a-blog.png"
    ]
}
</script>
```

Rich schema is added to all posts by default, but you can turn it off at <strong>Console &rarr; Settings &rarr; SEO</strong>.

<DocsImage
	src="/images/docs/seo/rich-schema-tags.png"
	alt="Rich Schema Tags"
	style="max-height:400px"
/>

<h2 id="canonical">Canonical URLs</h2>

Hyvor Blogs follows one principle when it comes to canonical URLs: <strong>One URL for one content</strong>. A post will be accessible from only one URL. This basic principle helps you avoid common
duplicate content issues and helps search engines to understand your blog better.

<h3 id="custom-post-canonical">Custom Post Canonical URLs</h3>

In some cases, you may want to published a post published somewhere else on your blog. In such
cases, you can set a custom canonical URL for the post in the post editor.

<DocsImage
	src="/images/docs/seo/canonical-setting.png"
	alt="Canonical URL Setting"
	style="max-height:400px"
/>

<h2 id="robots">Robots.txt</h2>

Robots.txt is a file that tells search engine crawlers what pages to access and not. Hyvor Blogs
comes with a default robots.txt, which should be sufficient for most blogs.

```
User-agent: *
Sitemap: {{ _blog.base_url }}/sitemap.xml
Disallow: /p/
```

Default robots.txt file is as follows:

- `User-agent: *` - allows all crawlers to access your blog.
- `Sitemap: {{ _blog.base_url }}/sitemap.xml` - tells crawlers where to find the sitemap.
- `Disallow: /p/` - prevents crawlers from accessing /p/ routes (post preview pages).

You can update your robots.txt file in **Settings → SEO → Robots.txt**. You
can also use [theme variables](/docs/themes-templates#variables) there (ex:
`{{ _blog.base_url }}` in the default robots.txt file).

<h2 id="sitemap">Sitemap</h2>

Hyvor Blogs generates sitemaps automatically. You can find the <a href="https://www.sitemaps.org/protocol.html#index" target="_blank" rel="nofollow">sitemap index</a>
at `/sitemap.xml` of your blog. You may submit this URL to search engines to help them discover
your blog faster.

Sitemap index format:

```
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap><loc>https://blog.hyvorblogs.io/sitemap-pages.xml</loc></sitemap>
    <sitemap><loc>https://blog.hyvorblogs.io/sitemap-posts-1.xml</loc></sitemap>
    <sitemap><loc>https://blog.hyvorblogs.io/sitemap-posts-2.xml</loc></sitemap>
</sitemapindex>
```

The sitemap index links to other sitemaps of the blog.

- `sitemap-pages.xml` - contains links to the pages and homepage.
- `sitemap-posts-[index].xml` - contains links to posts. Each file can have up to 2500 URLs. First page has the oldest URLs. Within the file, we also auto generate
  - `<image:image>` to link to images in the post (only directly uploaded images)
  - `<xhtml:link>` to link to language variants of the post

Here is an example `sitemap-posts-[index].xml`.

```
<?xml version="1.0" encoding="UTF-8"?>
<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <url>
        <loc>https://blog.hyvorblogs.io/hello-world</loc>

        <xhtml:link rel="alternate" hreflang="en" href="https://blog.hyvorblogs.io/hello-world" />
        <xhtml:link rel="alternate" hreflang="fr" href="https://blog.hyvorblogs.io/fr/hello-world" />

        <image:image><image:loc>https://blog.hyvorblogs.io/media/hello-world.png</image:loc></image:image>
    </url>
</urlset>
```

<h2 id="prevent-indexing">Prevent Indexing</h2>

This is the meta tag you can use to prevent search engines from indexing a page.

```
<meta name="robots" content="noindex">
```

<h3 id="whole-blog">1. Whole blog</h3>

To prevent search engines from indexing your **whole blog**, turn off the
**Settings → SEO → Allow Indexing**
option. Hyvor Blogs will add the above meta tag to all pages. You can also add this meta tag manually
to your theme or head [custom code](/docs/custom-code).

<h3 id="whole-blog">2. Specific post</h3>

To prevent a post from indexing, you can add the above meta tag to the post's head [custom code](/docs/custom-code#post).

<h3 id="posts-of-tag">3. Posts of a tag</h3>

Sometimes you may want to prevent indexing posts that has a specific tag (ex: `no-index`). In such cases, you can add the above meta tag to the
[tag's custom code](/docs/custom-code#tag).
