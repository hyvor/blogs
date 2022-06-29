# Self-hosting using Builds

> We currently do not support builds on blogs that have 1GB+ media.

At HB, the term "**build**" is used for the process of generating a single zip file for your whole blog. The folders and files in this zip file represents the [routes](routes) in your blog.


Build Settings: **Console &rarr; Settings &rarr; Build**

## Manual Building

You can manually generate a build from the console and host it on any hosting provider that supports files hosting. However, manually doing this every time when there are updates on your blog is not.


## Hosting on Third-party Platforms

The following platforms are supported for deploying your blog as a static website.

* Netlify
* AWS S3

You can set up [Netlify](https://www.netlify.com/) in the Console. We will then automatically build your website on each update and will update your Netlify website.

## Hosting on your own servers

To host on your own servers, 

1. Set up a [webhook](webhooks) for `cache` and `cache.all` events. 
2. In the webhook handler (controller), call our [Console API](/console-api)'s `/build` endpoint and download the build file.
3. Then, you can do whatever you want with the zip file you get, for example:
    * Host it on the same server
    * Upload it to another third-party server for hosting

> Note that HTML files have the `.html` extension even the routes do not have the extension. Therefore, you will need to configure your server to look for `/post.html` when it gets a request to `/post`.

