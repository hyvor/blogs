# Publishing Themes

Are you ready to publish your newly built theme to our [themes list](/themes)? To do that, your theme should be developed within a fork of our [hyvor-blogs-themes](https://github.com/hyvor/hyvor-blogs-themes) repository. After everything is completed, send us a pull request to the `main` branch. If merged, your theme will be automatically added to our theme list, and other bloggers can install it easily.

> All themes in our themes list are free. If you wish to create a paid theme, you have to sell it outside our platform, and users can upload the ZIP from the Console to install it.

## Checklist {#checklist}

All the following requirements should be met in order to publish a theme to our official themes list.

* These files should be added:
  * index.twig
  * post.twig
  * tag.twig
  * author.twig
  * 404.twig
* Supports multi-languages (has a language switcher)
* Supports both light and dark color modes
* Respects the blog's color mode settings
* Pagination
* Blog search must be implemented at least with post searching. Optionally, you can add search for tags and authors.
* All posts should have links to translated versions of them (if available). Ex: "This post is also available in..." or "Translations: ..."
* All [placeholders](themes-templates#placeholders) are added
  * `_head`
  * `_foot`
  * `_comments`
  * `_newsletter`
* `_comments` should only be added to posts, not pages.
* `_comments` and `_newsletter` blocks should not be displayed if the value of each is empty.
* If the blog has a logo (`_blog.logo_url`), the logo should be shown in the header linking the logo to the blog homepage.
* [Internationalized](themes-internationalization)
* `<html lang="{{ _lang.code }}" dir="{{ _lang.direction }}">` is added
* Supports RTL languages. See [RTL Support](#rtl) below.
* Configurations are added for colors, fonts, etc. See [Config](#config) section.
* Configuration definitions (`config.def.yaml`) are added. See [Configuration -> Config Definitions](themes-config#config-def) page. Use the <a href="/config" target="_blank">config tool</a> to validate `config.def.yaml`.
* YAML files should use 2 spaces per indentation (not tabs, not 4 spaces).
* Mobile responsive
* Featured posts may have some unique UI in the index page (ex: a pinned/star icon)
* Content styles are added. See [Content styles](#content-styles) section below.
* All assets (JS, fonts, etc.) should be added in the `assets` folder. Do not load assets from external sources like Google Fonts.
* Should support the blog's social media links (shows an icon or link to the social media profile if the link is available)
  * Facebook
  * Twitter
  * Linkedin
  * Youtube
  * TikTok
  * Instagram
  * Github

## Config {#config}

As explained in the [configurations](themes-config) page, the following configurations are required when publishing your theme.

```yaml
THEME_NAME: my-theme
THEME_VERSION: 1.0.0
```

The following configurations are recommended for all published themes.

```yaml
colors:
  light:
    # ... colors for the light theme
  dark:
    # ... colors for the dark theme

# if only one font
font:
  size: 16px
  line_height: 1
  family: 'Inter, sans-serif'
  
# if multiple fonts
fonts:
  body:
    size: 16px
    family: 'Inter, sans-serif'
  heading:
    size: 24px
    family: 'Nunito, sans-serif'

settings:
  loop: # features in the index page (list of posts)
    authors: true
    tags: true
    featured_image: true
  post: # features in the post page
    authors: true
    tags: true
    featured_image: true
    toc: true # table of contents
  feed: true # a link to RSS feed (if available)
```

## RTL Support {#rtl}

All published themes should support RTL (right-to-left) languages. Follow these tips to make sure your theme supports RTL.

* Add `dir="{{ _lang.direction }}"` to the `<html>` tag
* Use direction-aware CSS properties when adding horizontal padding, margin, and left/right borders.

| Do not use      | Use this |
|-----------------|-------------------------|
| `padding-left`  | `padding-inline-start`  |
| `padding-right` | `padding-inline-end`    |
| `margin-left`   | `margin-inline-start`   |
| `margin-right`  | `margin-inline-end`     |
| `border-left`   | `border-inline-start`   |
| `border-right`  | `border-inline-end`     |

* Make sure absolute/fixed positioned elements are positioned correctly in RTL mode
* `<pre><code>` blocks should have `direction: ltr` CSS
* Make sure to add an RTL language to your DEV blog and test RTL support

## Content Styles {#content-styles}

All published themes should nicely style all the blocks in the "Content Style Guide" post in your DEV blog. In addition to styling them, follow these guidelines to avoid common but subtle UX issues.

#### 1. Heading Anchors {#heading-anchors}

We automatically add anchors to headings that have an `id` attribute.

HTML without ID:

```html
<h1>My Heading</h1>
```

HTML with ID:

```html
<h1 id="heading-anchor">
    <a href="#heading-anchor" class="heading-anchor">
        My Heading
    </a>
</h1>
```

These heading anchor should be **styled differently** from other links. For example, you can add a `#` or an SVG image (via `background-image`) before the anchor text.

```css
h1, h2, h3, h4, h5, h6 {
    a[href^="#"] {
        
        /* Remove usual link styles */
        color: inherit;
        text-decoration: none;
        position: relative;
        
        /* Add different styles */
        &:hover:before {
            content: "#";
            position: absolute;
            right: 100%;
            margin-right: 5px;
            color: var(--color-text-content-secondary);
        }
    }
}
```

#### 2. Code Blocks {#code-blocks}

* Code blocks should have `tab-size: 4`
* Code blocks should have `direction: ltr`
* Line numbers should be absolutely positioned
* Add left padding when line numbers are enabled (check for `.has-line-numbers`).

And, line numbers should be absolutely positioned. Also, add left padding when line numbers are enabled (check for `.has-line-numbers`).

```css
pre {
    position: relative;
    tab-size: 4;
    direction: ltr;
    .line-number {
        margin-right: 1rem;
        position: absolute;
        left: 1rem;
    }
    &.has-line-numbers .line {
        padding-left: 2rem;
    }
}
```

#### 3. Tables {#tables}

* `.table-container` should have `overflow-x: auto` to make sure the table is scrollable on mobile devices

```css
.table-container {
    overflow-x: auto;
}
```

#### 4. Paragraphs {#paragraphs}

Margins must be handled carefully for paragraphs inside lists, tables, and blockquotes. The following works well in most cases.

```css
li {
    p {
        margin-top: 0;
        &:last-child {
            margin-bottom: 0;
        }
    }
}
```



## Versioning {#versioning}

The first version of the theme should be `1.0.0`. After that you can increment the version number according to the changes you make. For a patch (ex: bug fix), you can use `1.0.1`, `1.0.2`, etc. For a minor change, you can use `1.1.0`, `1.2.0`, etc. Unlike other software, themes do not have significant changes breaking changes. Therefore, we do not think you will ever need a major version change.

## Change Log {#changelog}

Add a `CHANGELOG.md` file to your theme folder and add the changes for each version. See [keepachangelog.com](https://keepachangelog.com/en/1.1.0/) to learn how to write a changelog.

Once everything is completed, send us a pull request to [hyvor-blogs-themes](https://github.com/hyvor/hyvor-blogs-themes) repository. When the PR is merged, the themes list will automatically update with your new theme.