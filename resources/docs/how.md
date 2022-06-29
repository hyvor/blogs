# How HB works

> This is a technical overview of how Hyvor Blogs work for those who are interested. You do not need to know this to use the platform.

Hyvor Blogs is a SaaS (software-as-a-service) blogging platform. We take care of software updates, server maintenance, and security. HB allows you to create a customizable blog. When you creates a blog on our platform, you choose a subdomain, which will be used to identify the blog within HB.

Let's say you created a blog with the subdomain `steve`. By default, your blog will be publicly available at `steve.hyvorblogs.io`. You can manage your blog at `blogs.hyvor.com/console/steve`.

## Serving the blog

Blogs are served from the `hyvorblogs.io` domain. When a user enters `steve.hyvorblogs.io/hello-world` on their browser, that request comes to our servers. We determine the blog using the subdomain (`steve` in this case). Then, we determine the resource type (what is the user requesting?) using the path (`/hello-world`).

## Posts

Posts are the main pieces of the blog. They are written by the blogger in the console and saved in our database with some meta data such as slug, title, description, featured image, etc. Posts are identified by the slug, which is unique within the blog.

## HTML Rendering

Let's say `/hello-world` is a post. The post content and meta data is saved in our database. Now, we want to combine it with the theme to create a HTML output. We call this process **HTML Rendering**. In HB, theme files are written in Twig, a superset of HTML. `post.twig` is responsible for rendering posts. So, when someone requests `/hello-world`, we find the data of the post with the slug `hello-world` and send it through `post.twig` to render the HTML. We send this HTML back to the user.

## Caching

We use CDN edge-caching to make blogs on HB faster for everyone. [Fastly](https://www.fastly.com/) is our global CDN. When sending that rendered HTML output to the user, we instruct Fastly to cache that response. Fastly caches that response **on all their data centers worldwide**. If any user requests that path afterwards, the content will be served from Fastly's cache from the data center nearest to the user. Users do not need to connect to our servers. They connect to the nearest Fastly data center to fetch content of your blog. This results in faster loading times regardless of user's location.

So, HTML rendering only happens one time per path. All subsequent requests are handled by our CDN. They never even reach our servers (this is why we cannot provide an in-built analytics feature for your blogs).

> We call this technique **first-request caching**, as we cache content in the first request. This was greatly inspired from the [works](https://dev.to/ben/making-devto-insanely-fast) of Ben Halpern of Dev.to.

### Cache Invalidation

What happens if data updates? If you update a post or its meta data, we have to clear cache to make sure users get the latest version. If that happens, we manually clear in-server and CDN caches. The next request will go through the HTML rendering step and save the new output in cache. All requests after that will be served through the cache.


[Learn HB theme development](themes-intro)