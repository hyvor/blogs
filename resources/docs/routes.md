# Routes

You will learn:
* Default routes of a blog
* 

Routes are used to configure how specific requests are handled. A route matches the request path and determines what output to send back to the user. For example, if the request path is `/tag/my-tag`, route determines that it should render `tag.twig` and send response back to the user.

## Default Routes {#defaults}

A new blog will have these 6 default routes.

Route name | Match |  Description | Template | Posts Filter
---|---|---|---|---|---|---|---|
`post` | `/{slug}` | Matches a post | post | |
`page` | `/{slug}` | Matches a page | page,post | |
`index` | `/` | Main index page (lists all posts) | index |
`tag` | `/tag/{slug}` | Tag index page (lists all posts of a specific tag) | tag,index | `tag.slug = {slug}`
`author` | `/author/{slug}` | Author index page (lists all posts of a specific author) | author,index | `author.slug = {slug}`
`search` | `/search/{search}` | Search results page (lists all posts that matches the current search term) | search,index  | |
 
> Posts Filter is a [FilterQ expression](https://github.com/hyvor/laravel-filterq) to filter posts. These filtered posts will be sent to the template as the `_posts` variable. It is only used in listing pages like index, tag, author. Matched params (`{slug}`) can be used in this expression.

In addition to these default routes, there are some special, non-customizable routes.

Match | Description
---|---
`/styles.css` | The main CSS file of the blog (auto-generated from SCSS files in theme styles)
`/assets/{file_name}` | To serve files in the theme **assets** directory
`/media/{file_name}` | To serve uploaded media files
`/p/{hash}` | To preview posts and pages
`/robots.txt` | Robots.txt file (customizable in settings)
`/sitemap.xml` | Blog's sitemap index file

## Changing Post/Page Permalinks {#permalinks}

You can change the **match** value of `post` and `page` routes to change post/page permalinks. By default, it looks like `/{slug}`. You may change it to a different structure which may have date, tag, and/or author name. Here are some examples.

* `/{year}/{month}/{day}/{slug}`
* `/{tag}/{slug}`
* `/{author}/{slug}`

Note that `{slug}` is always required. The following placeholders are supported.

Basic placeholders:

Placeholder | Description |
---|---|--- |
`{slug}` | Post slug (required always) |
`{tag}` | Slug of the first tag of the post |
`{author}` | Slug of the first author of the post |

> Please note that if you use `{tag}` (or `{author}`) in the post/page URL, all post **should have** at least one tag (or author). Otherwise, the post/page URL will show 404 error.

Time-based placeholders:
* Represent the post's **publish time**
* Only English lowercase is supported for month and day names

Placeholder | Description | Example
---|---|--- |
`{year}` | 4-digit year | `2022`
`{year_short}` | 2-digit year | `99` or `22`
`{month}` | 2-digit month number | `01` to `12`
`{month_number}` | month number without leading zero | `1` to `12`
`{month_short}` | short month name | `jan` to `dec`
`{month_long}` | long month name | `january` to `december`
`{day}` | 2-digit day | `01` to `31`
`{day_number}` | day without leading zero | `1` to `31`
`{day_year}` | ordinal day of the year | `1` to `365`
`{day_week}` | 3-letter weekday | `mon` to `sun`
`{day_week_long}` | weekday | `monday` to `sunday`
`{day_week_number}` | weekday as a number | from `1` to `7`
`{hour}` | hour of the day, in 24-format | `00` to `23`
`{minute}` | minute of the hour | `00` to `59`
`{second}` | second of the minute | `00` to `59`
`{unix}` | UNIX timestamp in seconds | `1448406000`

## Customizing other default routes

Similar to `post` and `page` routes, other default routes (`index`, `tag`, `author`, and `search`) routes are customizable to some extent. Here are some ideas:

* `/author/{slug}` &#8594; `/creator/{slug}`
* `/tag/{slug}` &#8594; `/category/{slug}`
* `/search/{search}` &#8594; `/find/{search}`
* `/` &#8594; `/blog`

## Custom Routes {#custom}

In addition to the default 6 routes, you can add your own routes. Some examples use cases are:

* Creating custom landing pages.
* Creating new post collections.
* Creating custom RSS feeds, for example, for a podcast.

> Please note that you (or theme developers) can also add custom routes by adding `route-{route}.twig` files to template files. See [here](themes-overview#custom-routes) from more details.


## Route Conflicts

Usually, route conflicts can happen when two or more routes has the **same match** value. In Hyvor Blogs, `post` and `page` routes can have the same match values. (You can see, the default values of those two routes are the same: `/{slug}`). However, other routes match cannot have duplicate match values.

## Turning off routes

You can turn off routes except the `post` and `page` routes. Turning off routes will remove those pages from your blog. You can turn them on later.

## Suffixes & Prefixes

These suffixes are supported:

* `/feed` - For the atom feed (`posts_filter` should be set)
* `/page/{page_number}` - For pagination

Matches can be prefixed with a language code. See [languages](languages) for setting up multiple languages on your blog.


## Building a website {#website}

We usually call a website "a blog" when it has posts and the home page lists all of them. That is the default behavior of Hyvor Blogs. Even out of its purpose, you can use Hyvor Blogs to create a general website. For example, you can create a landing page for the homepage, and have your blog in the `/blog` subdirectory.

See our [build a website](/blog/website-with-hyvor-blogs) tutorial on our blog.

## Multiple Post Collections {#collections}

By default, your blog has one post collection, and all posts will be listed the index page. What if you want to have to separate collections, for example, blog posts and podcast episodes in the blog? You can use routes and [tags](tags) to achieve this.

* We add a `podcast` tag to the all podcast posts.
* We can customize the `index` (`/`) route and its template (`index.twig`) to show an overview of blog posts and podcast episodes. To do this, you will need to update `index.twig` and use our [Data API](api-data) to fetch posts separately.
* We add two new routes with a new template:
  * `/blog` -> to list blog posts (using the filter `tag.slug != podcast`)
  * `/podcast` -> to list podcast episodes (using the filter `tag.slug = podcast`)

See [multi-collection blog](/blog/multi-collection-blog) tutorial on our blog.
