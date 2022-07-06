# SEO

You will learn:

* How Hyvor Blogs take care of technical SEO
* About Robots.txt and how to customize it
* About Sitemaps and how HB auto generate them


SEO settings: **Console &rarr; Settings &rarr; SEO**.


## Robots.txt

Robots.txt is a file that tells search engine crawlers what pages to access and not.

## Sitemaps {#sitemaps}

Hyvor Blogs auto-generates sitemaps. The [sitemap index](https://www.sitemaps.org/protocol.html#index) is at the `/sitemap.xml` path of your blog. You can submit this file to search engines. 

The file contains something like this:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap><loc>https://blog.hyvorblogs.io/sitemap-pages.xml</loc></sitemap>
    <sitemap><loc>https://blog.hyvorblogs.io/sitemap-posts-1.xml</loc></sitemap>
    <sitemap><loc>https://blog.hyvorblogs.io/sitemap-posts-2.xml</loc></sitemap>
</sitemapindex>
```

The main sitemap links to other sitemaps of the blog.

* **sitemap-pages.xml** - contains links to the [pages](posts-pages#pages) and homepage.
* **sitemap-posts-x.xml** - contains links to posts. Each `-x` file can have up to 2500 URLs. First page has the oldest URLs. Within the file, we also auto generate
  * `<image:image>` tags to link to images in the post
  * `<xhtml:link>` elements to link to [language variants](languages) of the post
