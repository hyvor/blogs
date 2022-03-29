# Console API

The Console API allows you to do administrative tasks of a blog. This is the same API we use internally in the Console. You can use it to automate some tasks or even build a completely new mini-console by yourself.

## Calling the API

* API Basepath: **https&#65279;//blogs.hyvor.com/api/console/v0/blog/{subdomain}**
* Create a Console API Key from the Console and send it as the **API-KEY** header.
* Console API endpoints use the following HTTP methods.
  * `GET` - to get data, usually an array of resources
  * `POST` - to create a resource
  * `PATCH` - to partially update a resource
  * `PUT` - to completely update an resource
  * `DELETE` - to delete a resource

## Categories

The Console API is huge, and is categorized by what "resource" you want to access or manage. Most categories have CRUD operations but some may have more endpoints for specific tasks. Similarly to our [Data API](api-data), the Console API always return an object or an array of objects. These objects are defined within the Category. Also, note that Console API objects are different from Data API objects.

Jump to each category:

* [Posts](#posts)
* [Pages](#pages)
* [Tags](#tags)
* [Users](#users)
* [Media](#media)
* [Navigation](#navigation)
* [Languages](#languages)
* [Redirects](#redirects)
* [Webhooks](#webhooks)
* [Theme Files](#theme-files)
* [Data Import](#data-import)
* [Data Export](#data-export)
* [Subscription](#subscription) (Billing)
* [Blog Settings](#blog-settings)
* [Blog Meta Data](#blog-meta)
* [Other Endpoints](#other)

### Posts {#posts}

#### Post Object {#post-object}

```json
{
    "id": 2000,
    "preview_id": "...",
    "created_at": 1639655890,
    "updated_at": 1639655890,
    "published_at": null,
    "status": "draft",
    "is_featured": false,
    "is_page": false,
    "slug": "hello-world",
    "content": "{}",
    "content_unsaved": "{}",
    "title": "Hello World",
    "description": "Just saying hello to the world",
    "url": "https://myblog.hyvorblogs.io/hello-world",
    "featured_image": "https://myblog.hyvorblogs.io/media/image.png",
    "canonical_url": null,
    "reading_time": 2,
    "code_head": null,
    "code_foot": null,
    "tags": [ Tag Objects ],
    "authors": [ User Objects ]
}
```

#### GET /posts {#endpoint-posts}

This endpoint returns posts of the blog. You can use request params to filter results. 

Request Params:


| Name | Type | Description | Accepted Values
---|---|---|---|
`status` | `string` | Status of the post to filter | `all`, `published`, `draft`, `scheduled`, `featured` |
`author_id` | `null` or `integer` | Author ID to filter | |
`tag_id` | `null` or `integer` | Tag ID to filter | |
`language_id` | `null` or `integer` | Language ID to filter | |
`start_at` | `null` or `integer` | Start timestamp for filtering | UNIX Timestamp
`end_at` | `null` or `integer` | End timestamp for filtering | UNIX Timestamp
`search` | `null` or `string` | For searching | |

Response: An array of [Post Objects](#post-object)

#### GET /post/{id} {#endpoint-post}

Get a single post by ID. Returns a single post object.

#### PATCH /post/{id} {#endpoint-post-update}

Update a post by ID.