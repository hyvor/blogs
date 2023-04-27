# Languages

You will learn:

* How to change the primary language of your blog
* How to make your blog multi-language
* Technical SEO for multi-language blogs

Language settings: **Console &rarr; Settings &rarr; Languages**.

## Changing the primary language

**English** (`en`) is the primary language for newly created blogs. If you are blogging in a different language, it is important to change the language in Language settings to tell users, browsers, and crawlers the language of your blog.

In language settings, each language has a **code** and a **name**. 

* **Code**: The language code should be a valid HTML [`lang` attribute](https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/lang) value. Some examples are
  * `en`
  * `en-US`
  * `en-GB`
  * `fr`
  * `fr-FR`

* **Name**: The language name is text to explain the language code. We recommend you to write it in the native alphabet. Some [themes](theme) may use the name to show a message like "This post is translated into Español, 简体中文, and Nederlands".

## Multi-language Blogs

Hyvor Blogs comes with in-built support for multi-language blogs. Therefore, it is simple to set up a multi-language blog. Only thing you have to do is adding a new language in settings. Make sure you use a correct language code as explained above.

### Routes

Let's say you there are two languages `en` (default) and `fr` in your blog. Now, `/` will list all English posts. `/fr` will list all French posts. All [routes](routes) of your blog will be changed similarly.

### Writing Translated Posts

In the post editor, you will see an option to create multiple language variants of a post.

![Switching Between Variants](/img/docs/language-post-variants.png)

Think of each variant as a separate post. Each post can have a different slug. For example, if you have a post with slug `hello-world` in English, you can have a post with slug `bonjour-monde` in French. The French post will be shown at `/fr/bonjour-monde`.

### Technical SEO

Here are some under the hood works that Hyvor Blogs do to make sure search engine robots understand your multi-language pages.

HB adds the `lang` attribute to the `<html>` tag in all pages using the language code you set (this is why using the correct language codes are important).

```html
<html lang="en">
```

In addition, HB will add `hreflang` alternate tags. For example, if you have three languages (`en`, `fr`, `es`), the `en` index page (`/`) will have these tags.

```html
<link rel="alternate" href="https://yourblog.com/fr" hreflang="fr" />
<link rel="alternate" href="https://yourblog.com/es" hreflang="es" />
```

For posts, we will add these alternate tags **only if** we can find its translated posts.


<!-- ## Translating the Theme {#translate-theme} -->

