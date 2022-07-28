# SEO

Hyvor Blogs handles technical SEO for you.

* [Meta Tags](#meta)
* [Rich Schema](#rich-schema)
* [Canonical URLs](#canonical)
* [Robots.txt](#robots)
* [Sitemap](#sitemap)
* [Prevent Indexing](#prevent-indexing)

SEO settings: **Console &rarr; Settings &rarr; SEO**.

## Meta Tags {#meta}

HB adds the following meta tags to `<head>` of all pages of the blog. They help with search engine and social media previews.

```html
<!-- BASIC SEO -->
<title></title>
<meta name="description" content="" />
<link rel="canonical" href="" />

<!-- LANGUAGE VARIANTS -->
<link rel="alternate" href="" hreflang="" />
<link rel="alternate" href="" hreflang="" />

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
<meta property="article:author" />
<meta property="article:author" />
<meta property="article:section" />
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

## Rich Schema {#rich-schema}

Rich schema is coming soon.

## Canonical URLs {#canonical}

Canonical URLs are essential to prevent duplicate pages. HB takes cares of correctly generating canonical URLs for all routes in your blog. However, you may want to publish a post that has already been published elsewhere. In this case, you can set a custom canonical URL for the post. HB will then use this URL to generate the canonical [meta tag](#meta).

**Console &rarr; Post &rarr; Settings &rarr; Advanced &rarr; Canonical URL**

<p>
<img src="/img/docs/seo-canonical.png" alt="How to set canonical URLs in Hyvor Blogs" width="350" />
</p>

## Robots.txt {#robots}

Robots.txt is a file that tells search engine crawlers what pages to access and not. Hyvor Blogs comes with a default robots.txt, which should be sufficient for most blogs.

```twig
User-agent: *
Sitemap: {{ _blog.base_url }}/sitemap.xml
Disallow: /p/
```

The default file prevents search engines from indexing `/p/` routes, which are used for previewing posts (Those previews are private). You can update robots.txt at **Console &rarr; Settings &rarr; SEO &rarr; Robots.txt**. You have access to [route variables](themes-templates#variables) in the code.

## Sitemap {#sitemap}

Hyvor Blogs auto-generates sitemaps. The [sitemap index](https://www.sitemaps.org/protocol.html#index) is at the `/sitemap.xml` path of your blog. You can submit this file to search engines.

Sitemap index format:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap><loc>https://blog.hyvorblogs.io/sitemap-pages.xml</loc></sitemap>
    <sitemap><loc>https://blog.hyvorblogs.io/sitemap-posts-1.xml</loc></sitemap>
    <sitemap><loc>https://blog.hyvorblogs.io/sitemap-posts-2.xml</loc></sitemap>
</sitemapindex>
```

The sitemap index links to other sitemaps of the blog.

* **sitemap-pages.xml** - contains links to the [pages](posts-pages#pages) and homepage.
* **sitemap-posts-x.xml** - contains links to posts. Each file can have up to 2500 URLs. First page has the oldest URLs. Within the file, we also auto generate
  * `<image:image>` to link to images in the post (only directly uploaded images)
  * `<xhtml:link>` to link to [language variants](languages) of the post

Here is an example `sitemap-posts-x.xml`.

```xml
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

## Prevent Indexing {#prevent-indexing}

You can prevent search engines from indexing your blog or specific pages by adding the following code within `<head>`.

```html
<meta name="robots" content="noindex">
```

| Where | How |
| -- | --- |
| Whole blog | Turn off **Console &rarr; Settings &rarr; SEO &rarr; Allow Indexing**. HB will then add the above code to all pages. Or, add the above code to head [custom code](custom-code#blog) of the blog.
| A post | Add the above code to the head [custom code](custom-code#post) of the post.
| Posts of a tag | Add the above code to the head [custom code](custom-code#tag) of the tag.

