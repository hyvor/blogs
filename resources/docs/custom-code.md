# Custom Code

* All custom code can contain HTML or Twig code and has access to [route variables](themes-templates#variables). 
* Head code will be added before the `</head>` tag. 
* Foot code will be added before the `</body>`. 
* There are three ways to add custom code to your blog.
    1. [Blog](#blog)
    2. [Post](#post)
    3. [Tag](#tag)
* Custom code is added in the **Blog -> Post -> Tag** order.

## 1. Blog {#blog}

You can add custom code to the whole blog. This way, the custom code will be added to all templates in your blog. This is useful for adding services like [analytics](analytics).

**Console &rarr; Settings &rarr; Custom Code**

## 2. Post {#post}

You can also add custom code a specific post only in Post Settings. The custom code will then only be added to that post.

**Console &rarr; Post &rarr; Advanced &rarr; Head/Foot Code**

## 3. Tag {#tag}

You can also define custom code for a tag. Then, this custom code will be added to the posts that has this tag.

**Console &rarr; Settings &rarr; Tags &rarr; Edit Tag &rarr; Custom Code**

For example, posts that have SVG animations will need an additional Javascript library to play SVG animations. You can assign a `svg` tag to those posts and link to the Javascript library in the custom code of the `svg` tag.

> In addition to these methods, you can also [edit the theme](theme#editing) directly.