# Export Content

You own your data, and it is our responsibility to make sure you can access them any time you need. You can export both data and media from the console.

To export: **Console &rarr; Settings &rarr; Import & Export**

## Content Export Formats {#formats}

We support exporting data in two formats:

* WordPress format (WXR)
* Hyvor Blogs format (JSON)

If you plan to leave Hyvor Blogs and use another platform, it is preferable to use the WordPress format, as most other platforms support importing content from WordPress export files. However, the WordPress format does not support some Hyvor Blogs features such as [multi-authors](users), [multi-languages](languages), and [redirects](redirects). Therefore, if you want a full backup of your content, we suggest exporting content in the Hyvor Blogs format.

## Monthly Exports {#monthly}

You can set up automatic monthly exports to Google Drive, Dropbox or a S3-compatible storage. A new zip file (`hyvor-blogs-{blog_name}-YYYY-mm.zip`) will be created in the storage folder you provide on the 1st of each month. The zip file will contain these:

* `content.json` - Content export in the Hyvor Blogs format
* `media` - A folder that contains all media files of your blog.


## Export Data Structure {#export-structure}

Hyvor Blogs exports data in JSON format with the following data structure.

```json
{
    "blog": { a blog object },
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
    "navigation": [
        navigation object
        ...
    ],
    "routes": [
        route object,
        ...
    ],
    "languages": [
        language object,
        ...
    ],
    "media": [
        media object,
        ...
    ],
    "redirects": [
        redirect object,
        ...
    ],
}
```

See [Console API Objects](api-console#objects) for the definitions of each object. The only difference is in the **Post Object**. In addition to `content` property, the Post Objects will have `content_html` and `content_markdown` properties with content converted into HTML and Markdown respectively.