# Publishing Themes

Are you ready to publish your newly built theme to our [themes list](/themes)? To do that, your theme should be developed within a fork of our [hyvor-blogs-themes](https://github.com/hyvor/hyvor-blogs-themes) repository. After everything is completed, send us a pull request to the `main` branch. If merged, your theme will be automatically added to our theme list, and other bloggers can install it easily.

> All themes in our themes list are free. If you wish to create a paid theme, you have to sell it outside our platform, and users can upload the ZIP from the Console to install it.

## Checklist

* Has at least these templates
  * index.twig
  * post.twig
  * tag.twig
  * author.twig
* Supports multi-languages
* All posts should have links to translated versions of them (if available). Ex: "This post is also available in..." or "Translations: ..."
* Supports both light and dark color modes
* Respects the blog's color mode settings
* Pagination
* All [placeholders](themes-templates#placeholders) are added
  * `_head`
  * `_foot`
  * `_comments`
  * `_newsletter`
* `_comments` should only be added to posts, not pages.
* `_comments` and `_newsletter` blocks should not be displayed if the value of each is empty.
* If the blog has a logo (`_blog.logo_url`), the logo should be shown in the header linking the logo to the blog homepage.
* [Internationalized](themes-internationalization)
* `<html lang="{{ _lang.code }}"` is added
* All [blocks](writing#blocks) are styled properly.
* Configurations are added for colors, fonts, etc.
* Mobile responsive
* Featured posts may have some unique UI in the index page (ex: a pinned/star icon)


## Config

As explained in the [configurations](themes-config) page, the following configurations are required when publishing your theme.

```plain
THEME_NAME: my-theme
THEME_VERSION: 1.0.0
```



## Versioning

Versioning is important. Use [semantic versioning](https://semver.org/).

Once everything is completed, send us a pull request to the . When the PR is merged, the themes list will automatically