# Self-Hosting using Delivery API

This guide explains how to self-host your blog using our [Delivery API](api-delivery) and [Webhooks](webhooks).

By default, your blog runs on a subdomain of **hyvorblogs.io**. You can also set up a [custom domain](custom-domain) (such as **myblog.com** or **blog.example.com**) easily. However, what if you wanted to host your blog on a subdirectory of your website (**example.com/blog**)? That process is not straightforward, and highly depends on how your website is configured.

## Using a reverse-proxy {#reverse-proxy}

The first option is configuring your web server to work as a reverse proxy when it gets requests to **/blog**. This is possible with most servers like Apache, NGINX, and Caddy. However, we do not support this method yet. If you are not able to use the Delivery API method as explained below and interested in using a reverse-proxy, contact us and let us know.

## Using Delivery API {#delivery-api}

[Delivery API](api-delivery) is a special API that takes a path as an input and returns a JSON object explaining **"how to serve it"**. You can use this concept to host your blog on a subdirectory of your website.

### How it works

Let's say someone requested `/blog/hello-world` on your website. Then, your server sends a request to our Delivery API and learn how to respond to this request (path is now `/hello-world` inside HB). Delivery API tells what content and headers to send back to the user. Then, your server caches the Delivery API response and sends the response back to the user. If you update the `/hello-world` post, there should be a way to clear cache. This is done using webhooks.

### Libraries {#libraries}

Currently, we have libraries for the following frameworks.

* **Laravel**
  * [Github](https://github.com/hyvor/hyvor-blogs-laravel)
  * [Blog Tutorial](https://blogs.hyvor.com/blog/laravel-blog)

>  🙏 We are looking for help to cover more frameworks. If you are an experienced package developer and like to work with us, please contact us. We can make a deal :)

The following frameworks are our next priorities.

* Ruby on Rails
* Express.js
* Django
* Flask

See library-specific guides on Github for more information on how to self-host one or more blogs inside your web applications. Generally, you have to import those library to your project and tweak some configurations to make it work. They take care of calling the delivery API, caching, and cache invalidation.

Other platform guides:

* [Cloudflare Workers](https://blogs.hyvor.com/blog/blog-on-cloudflare-worker)
* [Fastly Compute@Edge](https://blogs.hyvor.com/blog/blog-on-fastly-compute-edge)

## Headless

It is also possible to use Hyvor Blogs as a headless content management system (CMS) along with tools like Gatsby, using our [Data API](api-data). However, this is not officially supported. We do not have any plans to support it in the near future.