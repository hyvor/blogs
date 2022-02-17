# SEO

You will learn:

* How Hyvor Blogs take care of technical SEO
* About Robots.txt and how to customize it
* About Sitemaps and how HB auto generate them


SEO settings: **Console &rarr; Settings &rarr; SEO**.


## Robots.txt

Robots.txt is a file that tells search engine crawlers what pages to access and not.

## Sitemaps {#sitemaps}

HB auto-generates sitemaps. The main sitemap file is at `/sitemap.xml` in the root of the blog. If you open it, you will see something like this:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>https://blog.hyvorblogs.io/sitemap-listing.xml</loc>
        <lastmod>2022-02-01T00:00:00+00:00</lastmod>
    </sitemap>
    <sitemap>
        <loc>https://blog.hyvorblogs.io/sitemap-1.xml</loc>
        <lastmod>2022-02-01T00:00:00+00:00</lastmod>
    </sitemap>
    <sitemap>
        <loc>https://blog.hyvorblogs.io/sitemap-2.xml</loc>
        <lastmod>2022-02-01T00:00:00+00:00</lastmod>
    </sitemap>
    <sitemap>
        <loc>https://blog.hyvorblogs.io/sitemap-media-1.xml</loc>
        <lastmod>2022-02-01T00:00:00+00:00</lastmod>
    </sitemap>
</sitemapindex>
```

The main sitemap links to other sitemaps of the blog.

* **sitemap-listing.xml** - This file contains links to the index page, static pages, tag pages, author pages, and other custom post-listing pages.
* **sitemap-x.xml** (Ex: `sitemap-1.xml` or `sitemap-2.xml`) - These files contains links to posts. Each file can have up to 2000 URLs.
* **sitemap-media-x.xml** - This file contains links to media files in the blog (2000 per each sitemap).