# Self-Hosting with Web Frameworks

So, your website is built using a modern web framework like Laravel? You can easily set up your blog to be served on a subdirectory (`/blog`) rather than the default subdomain (`blog.hyvorblogs.io`) or a custom domain. Our [Delivery API](api-delivery) does this magic with the help of [Webhooks](webhooks).

## How it works

#### Step 1: We made your blog static and cacheable

Throughout this documentation, you may have seen that we try to make your blog as static as possible. We made that decision to make your blog **cacheable**. Simply saying, your whole blog can "live" in a cache like filesystem or Redis. Not just posts, but also media. Because your blog is static, you do not need a dynamic programming language to process your website (That is the reason we do not support features like [memberships](memberships) and [analytics](analytics) natively).

#### Step 2: The Magical Delivery API

[Delivery API](api-delivery) is an original idea of ours. It takes a path in your blog and returns a JSON response describing how to "serve" it. For example, if you provide the `/` path, the Delivery API will tell you that is the index page and what content to show, with what content type. So, you get the idea now? When you get a request to your website's `/blog/{path}`, you can call our Delivery API and create an HTTP response using the JSON data of the Delivery API and send it back to the user. Calling the Delivery API everytime is slow and bad for your blog's speed. So, we cache the response after the first time. So, if the user requests the `/` path again, the response will be sent from the cache, which is super-fast!

But, when you update a post or edits the theme, how do we clear the cache?

#### Step 3: Bringing Webhooks to the scene

Whenever something changes on your blog (when you take an action through the Console), we will send a webhook to your web framework to clear the cache. That way, we make sure outdated content is not served. Cache clearing is also optimized to make sure only the required parts are cleared, in order to make the need for re-fetching as little as possible.

### Libraries {#libraries}

While all those implementation details are interesting, we have created libraries for popular web frameworks to make it easy for you to host a blog on a subdirectory.

Currently, we have libraries for the following frameworks.

* **Laravel**
  * [Github Repository](https://github.com/hyvor/hyvor-blogs-laravel)
  * [Blog Tutorial](https://blogs.hyvor.com/blog/laravel-blog)

>  🙏 We are looking for help to cover more frameworks. If you are an experienced package developer and like to work with us, please contact us. We can make a deal :)

The following frameworks are our next priorities.

* Ruby on Rails
* Express.js
* Django
* Flask

See library-specific guides on Github for more information on how to self-host one or more blogs inside your web applications. Generally, you have to import those library to your project and tweak some configurations to make it work. They take care of calling the delivery API, caching, and cache invalidation.

<!--## Headless

It is also possible to use Hyvor Blogs as a headless content management system (CMS) along with tools like Gatsby, using our [Data API](api-data). However, this is not officially supported. We do not have any plans to support it in the near future. -->