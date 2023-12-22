# Embedding

A blog created with Hyvor Blogs can be embedded to a website. Let's assume you already have a website built with a website builder like Wix, Squarespace, or Webflow. And, you want to have a blog at `/blog` path of your website while preserving the layout of your website. You can embed the blog to do that.

## How to embed

* Turn on Embeddable option of your blog: **Console &rarr; Settings &rarr; Hosting &rarr; Embeddable**.
* Add the following HTML code to your website. Make sure to replace `YOUR_SUBDOMAIN` with the subdomain of your blog.

```html
<div id="hyvor-blogs-embed-wrap"></div>
<script src="https://blogs.hyvor.com/embed/embed.js?subdomain=YOUR_SUBDOMAIN"></script>
```

If you add this script to the `/blog` page, the index page of your blog will be rendered at `/blog`. Other pages will be rendered using URL a query string. For example, if your blog has a `hello-world` post, it will be rendered at `/blog?p=hello-world`.

The blog is rendered inside an [iframe](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/iframe). Therefore, CSS and Javascript are not leaked from your website to the blog or vice versa.

## Advantages {#advantages}

* Embedding allows you to keep your main site's layout (header and footer), which feels native.
* Embedding is the only way to add have a blog within your domain to a website when you do not have access to the back-end (ex: website builders).

## Disadvantages

* Embedding is slightly slower than direct rendering because of Javascript and iframe usage.
* Query-based URLs are not the best-looking.
* [Flashload](https://github.com/hyvor/flashload) is disabled when embedding.
* Because content is rendered inside an iframe, and meta tags are added dynamically, search engines crawling and social media previews may have issues. According to some sources (unverified) most popular search engines do understand dynamically added meta tags. But, most social platforms are not able to understand OG/Twitter tags.

## SEO

Iframes have been historically discouraged when it comes to SEO. However, recently search engines have improved to index content inside iframes. In fact, Google [recently announced](https://developers.google.com/search/blog/2022/01/robots-meta-tag-indexifembedded) a new robots tag `indexifembedded` specially for iframe-embedded content, which we add to your blog's iframe. Yet, most other search engines may not index your pages correctly.

We are working on doing a first party research on the capabilities of search engines to index content inside iframes. If you have any concerns, we can use a [custom domain](custom-domain) for hosting your blog instead of embedding.