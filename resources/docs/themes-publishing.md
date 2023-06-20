# Publishing Themes

Are you ready to publish your newly built theme to our [themes list](/themes)? To do that, your theme should be developed within a fork of our [hyvor-blogs-themes](https://github.com/hyvor/hyvor-blogs-themes) repository. After everything is completed, send us a pull request to the `main` branch. If merged, your theme will be automatically added to our theme list, and other bloggers can install it easily.

> All themes in our themes list are free. If you wish to create a paid theme, you have to sell it outside our platform, and users can upload the ZIP from the Console to install it.

## Checklist {#checklist}

All the following requirements should be met in order to publish a theme to our official themes list.

* Has at least these templates
  * index.twig
  * post.twig
  * tag.twig
  * author.twig
* Supports multi-languages (has a language switcher)
* Supports both light and dark color modes
* Respects the blog's color mode settings
* Pagination
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
* Content Styles:
  * All [blocks](writing#blocks) are styled properly. You can test this with the "Content Style" post in your DEV blog.
  * Code blocks (`<pre><code>`) should have `tab-size: 4`
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
* Make sure to add a RTL language to your DEV blog and test RTL support

## Versioning {#versioning}

The first version of the theme should be `1.0.0`. After that you can increment the version number according to the changes you make. For a patch (ex: bug fix), you can use `1.0.1`, `1.0.2`, etc. For a minor change, you can use `1.1.0`, `1.2.0`, etc. Unlike other software, themes do not have significant changes breaking changes. Therefore, we do not think you will ever need a major version change.

## Change Log {#changelog}

Add a `CHANGELOG.md` file to your theme folder and add the changes for each version. See [keepachangelog.com](https://keepachangelog.com/en/1.1.0/) to learn how to write a changelog.

Once everything is completed, send us a pull request to [hyvor-blogs-themes](https://github.com/hyvor/hyvor-blogs-themes) repository. When the PR is merged, the themes list will automatically update with your new theme.