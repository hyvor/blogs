<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
    import { Callout } from '@hyvor/design/components';
</script>

# Export Data

You can export your blog data at any time using the export feature in the Console. The exported data will include all your posts, tags, users, and other relevant information. The export is provided in JSON format.

<Callout type="info" title="Exporting Media">
    The export file does not include media (uploaded images, videos, etc.) files themselves, only metadata about the media. To get an export of all media files, please contact support.
</Callout>

<h2 id="how">How to export</h2>

- Go to **Tools &rarr; Export** in the Console
- Click on **Export Now** button

It will take a couple of minutes depending on the size of your blog. You can track the progress in the **History** tab.

<DocsImage src="/images/docs/export/export.gif" alt="Exporting data" />

<h2 id="format">Export Format</h2>

The exported JSON file has the following structure:

```js
{
    "blog": { a blog object },
    "languages": [
        language object,
        ...
    ],
    "posts": [
        {
            "post": post object,
            "variants": [
                post variant object,
                ...
            ]
        },
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

All the objects are in the same format as the [Console API](/docs/api-console).

- [Blog Object](/docs/api-console#blog-object)
- [Language Object](/docs/api-console#language-object)
- [Post Object](/docs/api-console#post-object)
- [PostVariant Object](/docs/api-console#post-variant-object)
- [User Object](/docs/api-console#user-object)
- [Media Object](/docs/api-console#media-object)
- [Tag Object](/docs/api-console#tag-object)
- [Navigation Object](/docs/api-console#navigation-object)
- [Route Object](/docs/api-console#route-object)
- [Redirect Object](/docs/api-console#redirect-object)

Note: Each entry in `posts` pairs a **Post Object** with a `variants` array of **PostVariant Objects**, one per language. These variants have an additional `content_html` property with content converted into HTML.
