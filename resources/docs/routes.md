# Routes

You will learn:
* Default routes of a blog
* 

Routes are used to configure how specific requests are handled. A route matches the request path and determines what output to send back to the user. For example, if the request path is `/tag/my-tag`, route determines that it should render `tag.twig` and send response back to the user.

## Default Routes {#defaults}

A new blog will have these 6 default routes.

Route name | Match |  Description | Posts Filter
---|---|---|---|---|---|---|---|
`post` | `/{slug}` | Matches a post | |
`page` | `/{slug}` | Matches a page | |
`index` | `/` | Main index page (lists all posts) | `""`
`tag` | `/tag/{slug}` | Tag index page (lists all posts of a specific tag) | `tag.slug = {slug}`
`author` | `/author/{slug}` | Author index page (lists all posts of a specific author) | `author.slug = {slug}`
`search` | `/search/{search}` | Search results page (lists all posts that matches the current search term) | 
 
> Posts Filter is a [FilterQ expression](https://github.com/hyvor/laravel-filterq) to filter posts. These filtered posts will be sent to the template as the `_posts` variable. It is only used in listing pages like index, tag, author. Matched params (`{slug}`) can be used in this expression.

In addition to these default routes, there are some special, non-customizable routes.

Match | Description
---|---
`/styles.css` | The main CSS file of the blog (auto-generated from SCSS files in theme styles)
`/assets/{fileName}` | To serve files in the theme **assets** directory
`/media/{fileName}` | To serve uploaded media files

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

You can also change the `index` route to match `/blog`, not `/`. If you do this, you have to make sure you have a landing for `/`. See custom routes below to learn how to do that. 
## Custom Routes {#custom}

In addition to the default 6 routes, you can add your own routes.

> Please note that you can also add custom routes by adding `route-{route}.twig` files to template files. See [here](themes-overview#custom-routes) from more details.