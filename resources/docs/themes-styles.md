# Styling Themes


This folder contains SCSS files. `index.scss` is required. 

While developing and working with other blogging platforms/CMSs, we understood that customizing a theme becomes really hard when the theme developer puts all CSS in a single file. Therefore, we decided that we want to support "chunk-css" files to make it easy to edit for the blogger. And, we use [SCSS](https://sass-lang.com/) instead of CSS to make the theme developer's life easier. All CSS is valid SCSS. So, if you haven’t use SCSS earlier, just use CSS. SCSS just have some cool features like nesting rules. 

Back to “chunk-css”. let’s say you make a partial file for the blog header (`templates/_header.twig`). Then create an SCSS file to hold its CSS (`header.scss`). This pattern makes understanding and editing easier for the blogger. Finally, import all chunk files to `index.scss` using `@import` statements.

```scss
@import 'css-variables.scss';
@import 'header.scss';
@import 'body.scss';
```

On our side, we process `index.scss` file and generate a `styles.css`, which will be accessible via `/styles.css`. **That is the only CSS file of the whole blog**!

And, don't worry about using vendor prefixes like `-webkit-`. We auto-prefix the `styles.css` file before sending it to the user.

> We strongly encourage you to write CSS from scratch without using any libraries like Bootstrap. A blog theme is very simple and it is totally possible to manage everything on your own without depending on third-party libraries. If you really want to use a library, add it to assets instead of styles.

## Fonts

In the future, we have some plans to introduce font selection to [theme config](themes-config) so that users can select the fonts they want.

## Advanced Nodes {#advanced-nodes}

"[Using the editor](editor)" page describes all supported nodes. We try to use the most basic HTML elements to represent each node. However, there are some advanced components that require some attention when writing styles.

#### Image {#image}

```twig
<figure>
    <img src="https://exmaple.com/image.png" />
    <figcaption>Here goes the caption</figcaption>
</figure>
```
Note that figcaption can be empty. So, check if margins look good when figcaption is not there.

#### Embed: Rich {#embed-rich}

```twig
<figure>
    <div class="rich-embed">
        {# embed HTML code goes here... #}
    </div>
    <figcaption>Here goes the caption</figcaption>
</figure>
```

#### Embed: Link {#embed-link}

```twig
<figure>
    <a class="rich-link">
        <div class="rich-link-details">
            <div class="rich-link-title">{{ data.title }}</div>
            <div class="rich-link-description">{{ data.description }}</div>
            <div class="rich-link-domain">{{ data.domain }}</div>
        </div>
        <div class="rich-link-thumbnail">
            <img src="{{ data.thumbnail }}" />
        </div>
    </a>
    <figcaption>{{ data.caption }}</figcaption>
</figure>
```

**TIP:** It is possible to change this HTML structure, by adding a `component-rich-link.twig` to `/templates` folder. The `data` object is as follows.
```json
{
    "url": "https://blogs.hyvor.com",
    "title": "Hyvor Blogs",
    "description": "A simple blogging platform",
    "domain": "blogs.hyvor.com",
    "thumbnail": "https://blogs.hyvor.com/thumbnail.png",
    "icon": "https://blogs.hyvor.com/icon.png",
    "site": "Hyvor Blogs",
}
```

#### Callout {#callout}

```twig
<aside style="background-color:#0000000;color:#ffffff">
    <mark></mark>
</aside>
```