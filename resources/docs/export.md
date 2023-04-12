# Export

You own your data, and we make sure you can access them any time you need. You can export both data and media from the console.

To export: **Console &rarr; Settings &rarr; Export**

<!--
## Content Export Formats {#formats}

We support exporting data in two formats:

* WordPress format (WXR)
* Hyvor Blogs format (JSON)

If you plan to leave Hyvor Blogs and use another platform, it is preferable to use the WordPress format, as most other platforms support importing content from WordPress export files. However, the WordPress format does not support some Hyvor Blogs features such as [multi-authors](users), [multi-languages](languages), and [redirects](redirects). Therefore, if you want a full backup of your content, we suggest exporting content in the Hyvor Blogs format.

## Monthly Exports {#monthly}

You can set up automatic monthly exports to Google Drive, Dropbox or a S3-compatible storage. A new zip file (`hyvor-blogs-{blog_name}-YYYY-mm.zip`) will be created in the storage folder you provide on the 1st of each month. The zip file will contain these:

* `content.json` - Content export in the Hyvor Blogs format
* `media` - A folder that contains all media files of your blog.

-->

## Export Data Structure {#export-structure}

Hyvor Blogs exports data in JSON format with the following data structure.

```json
{
    "blog": { a blog object },
    "languages": [
        language object,
        ...
    ],
    "posts": [
        post object,
        post object,
        post object,
        ...
    ],
    "users": [
        user object,
        ...
    ],
    "tags": [
        tag object,
        ...
    ],
    "media": [
        media object,
        ...
    ],
    "navigation": [
        navigation object
        ...
    ],
    "routes": [
        route object,
        ...
    ],
    "redirects": [
        redirect object,
        ...
    ],
}
```

All objects are from the [Console API](api-console)

* [Blog Object](api-console#blog-object)
* [Language Object](api-console#language-object)
* [Post Object](api-console#post-object)
* [User Object](api-console#user-object)
* [Media Object](api-console#media-object)
* [Tag Object](api-console#tag-object)
* [Navigation Object](api-console#navigation-object)
* [Route Object](api-console#route-object)
* [Redirect Object](api-console#redirect-object)

> In `variants` of the **Post Object**, there will be an additional `content_html` property with content converted into HTML in the export objects.