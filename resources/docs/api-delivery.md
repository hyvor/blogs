# Delivery API

Delivery API tells you information about how to "serve a request". This API is the backbone of [self-hosting on a subdirectory](self-hosting-delivery-api).

## Calling the API

Call the following endpoint to access the Delivery API.

`https://blogs.hyvor.com/api/delivery/v0/blog/{subdomain}{path}`

No, we are not missing the `/` between the `{subdomain}` and `{path}`. `{path}` should always start with `/`. Here's are some examples:


* **https&#65279;://blogs.hyvor.com/api/delivery/v0/blog/myblog/**<br>
  How to serve `/` path of `myblog`? Usually, you will get a response explaining how to serve the HTML response of the index page.
* **https&#65279;://blogs.hyvor.com/api/delivery/v0/blog/myblog/hello-world**<br>
  How to server `/hello-world` path of `myblog`? You may get a response explaining how to serve the HTML response a post or page.
* **https&#65279;://blogs.hyvor.com/api/delivery/v0/blog/myblog/sitemap.xml**<br>
  How to server `/sitemap.xml` path of `myblog`? You may get a response explaining how to serve the XML sitemap.

## Why?

So, why is there a Delivery API? Let's say you already have an app or website. You want to have to your blog on `/blog` subdirectory. There are couple of ways to set this up including using your web server as a [reverse proxy](self-hosting-delivery-api#reverse-proxy). However, messing up with server configurations is no fun. It would be much easier if you could set up the reverse proxy using the programming language we already use in our website. So, we created this JSON API to make things easier.

> Good to know! We use this API internally to serve your blog at ***.hyvorblogs.io**. For example, when we get a request at **myblog.hyvorblogs.io/path**, our subdomain-serving servers call the delivery API (which is in our main server) to know how to "response" to the request. Then, subdomain servers convert JSON to a real HTTP response and return it back to the user. 

You can easily set up self-hosting on subdirectory of your application using the [libraries](self-hosting-delivery-api#libraries) we provide for popular web frameworks. If your programming language or framework is not supported, you can build your own mini-library using this and [webhooks](webhooks) documentations.

## Response Object {#response-object}

Success response of the Delivery API is always an object of one of the following type.

### 1. File Type 

```json
{
    "type": "file",
    "content": "SGVsbG8gV29ybGQ=",
    "mime_type": "text/html",
    "cache": true,
    "status": 200
}
```

### 2. Redirect Type

```json
{
    "type": "redirect",
    "to": "https://example.com",
    "cache": true,
    "status": 301
}
```

| Key | Type | Description |
| --- | --- | --- |
| `type` | `string` | `"file"` or `"redirect"`
| `content` | `string` | Base-64 encoded content of the file
| `mime_type` | `string` | HTTP Mime Type of the file
| `cache` | `boolean` | Should cache this response? For example, this is `false` for search and preview [routes](routes)
| `status` | `int` | HTTP Status. Can be `200`, `301`, or `302`, or `404`
| `to` | `string` | URL of the redirect