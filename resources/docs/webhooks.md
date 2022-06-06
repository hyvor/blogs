# Webhooks

Each blog can have up to 5 webhooks. Each webhook has a URL and can subscribe to one or more of the following events.

| Event                  | Dispatched                                                                                |
|------------------------|-------------------------------------------------------------------------------------------|
| `blog.updated`         | Any setting of the blog is updated                                                        |
| &nbsp;                 | &nbsp;                                                                                    |
| `post.created`         | A new post is created                                                                     |
| `post.updated`         | A post is updated                                                                         |
| `post.deleted`         | A post is deleted                                                                         |
| `post.variant.created` | A post variant is created                                                                 |
| `post.variant.updated` | A post variant is updated                                                                 |
| `post.variant.deleted` | A variant is deleted                                                                      |
| `post.tags.changed`    | Tags assigned to a post are changed                                                       |
| `post.authors.changed` | Authors assigned to a post are changed                                                    |
| `page.{events}`        | (all post events are available for pages)                                                 |
| &nbsp;                 | &nbsp;                                                                                    |
| `tag.{events}`         | (all post events are available for tags, except `.tags.changed` and `.authors.changed`)   |
| `user.{events}`        | (all tag events are available for users)                                                  |
| &nbsp;                 | &nbsp;                                                                                    |
| `cache.single`         | When cache of a single path should be cleared (index.css, assets, media, etc.)            |
| `cache.templates`      | When cache of all template-generated paths should be cleared (index, posts, feeds, etc.). |
| `cache.all`            | When all cache should be cleared                                                          |

### Post Request Format

On each event, we call the URL you provided, via the HTTP POST method. The request will have a JSON body like this.

```json
{
    "key": "webhook_key",
    "time": 1645208678,
    "data": {

    }
}
```

Contents of the data object varies depending on the event type. It will contain one or more [Data API Objects](api-data#objects).

`blog.updated` event will have a [Blog Object](api-data#blog-object).

```json
{
    ...
    "data": {
        "blog": { data_api_blog_object }
    }
}
```

`post.created` and `page.created` will have a [Post Object](api-data#post-object)

```json
{
    ...
    "data": {
        "post": { data_api_post_object }
    }
}
```

`post.updated` and `page.updated` events will have two [Post Objects](api-data#post-object) describing the old and the current state of the posts. It will also include a `diff` object, containing only the keys and new values that changed.

```json
{
    ...
    "data": {
        "post_old": { data_api_post_object },
        "post": { data_api_post_object },
        "diff": { a diff object between post and post_old }
    }
}
```

For example, if the status changed, the `diff` object will look like the following. The value is the new value. If you want to know the old value, you can simply find it in the `post_old` object.

```json
{
    "status": "published"
}
```

`tag` and `user` events will have [Tag](api-data#tag-object) and [Author](api-data#author-object) respectively. They will also contain `tag_old` and `user_old` for update events. For example, data of the `tag.updated` will look like this.

```json
{
    ...
    "data": {
        "tag_old": { data_api_tag_object },
        "tag": { data_api_tag_object },
        "diff": { a diff object between tag and tag_old }
    }
}
```

`cache` event will have an array of paths that should be cleared from cache. Note that `cache.all` will not have any data.

```json
{
    ...
    "data": {
        "cache": [
            "/media/image.png",
        ]
    }
}
```

### Security

In the console, you can find a key for each Webhook you create. This key is sent in each response. You can use it to verify the webhook using a simple string comparison. Something like:

```js
if (request.post.key !== env.HB_WEBHOOK_KEY) {
    return "Unauthorized";
}
```

Keep this key secure.


### Response & Retries

We expect a **200 HTTP Response Code** from your server to mark the webhook as success. If we get any other response code or fail to reach your servers, we will retry to send the webhook for 5 more times after

* 1 minute
* 5 minutes
* 30 minutes
* 1 hour
* 5 hours

If we fail all, we will mark that webhook as failed and will no longer send it automatically. However, you can manually trigger it from the console later.