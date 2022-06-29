# Internationalization

We do not expect you to translate themes to multiple languages, but the theme **should be translatable**. It means that all strings in the template files should be translatable. This is a requirement if you are planning to [publish](themes-publishing) the theme.

> If you are creating a private theme for a single language blog, you may skip this part.

Internationalization is easy. Instead of writing this:

```html
<h1>Welcome</h1>
```

You have to write this:

```twig
<h1>{{ 'welcome' | lang }}</h1>
```

Here, `'welcome'` is a key in `en.yaml`. And, `lang` is a custom Twig filter defined by HB. HB will display the correct language based on the [language of the user's blog](languages).

### Lang folder

The `/lang` folder contains `.yaml` language files. A language file may look like this:

```yaml
welcome: Welcome
```

### English is required

English (`en.yaml`) is the default language and it is required. You may also define other languages. Language codes should be **[ISO 639-1 Codes](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).** 

Let's see another example:

```yaml
welcome: "Welcome to our blog"
usersCount: "* users"
byAuthor: "by {authorName}"
```

In Twig templates, use the `lang` filter to render these strings with placeholders replaced.

```twig
<h1>
	{{ 'welcome' | lang }}
</h1>

<p>{{ 'byAuthor' | lang(authorName=_author.name) }}</p>

<p>{{ 'usersCount' | lang(2) }}</span>
```

As you can see there are two placeholders types:

- `*`
    - Use if the string only has one input (in most cases, a number)
- `named`
    - example: `{authorName}`
    - Use if the string has more inputs or if the inputs are data that can be described in name.
    - You can have multiple named placeholders in the string.
    - In the lang Twig filter, use [named arguments](https://twig.symfony.com/doc/3.x/templates.html#named-arguments) to fill placeholders with real data.

### Conditional strings based on a number {#lang-by-number}

Sometimes you may want to display a different message when a number is zero, one, or more than one. Instead writing a bunch of if conditions, you may use the `lang_by_number` custom Twig filter.

```yaml
# en.yaml
posts_num_zero: No Posts
posts_num_one: 1 Post
posts_num_multi: "* Posts"
```

```twig
Number of posts: 
{{ _pagination.total | lang_by_number(
    zero="posts_num_zero",
    one="posts_num_one",
    multi="posts_num_multi"
) }}
```

`_pagination.total` is a number. The `*` in `posts_num_multi` will be replaced by the given number.

### How translations work

You may take a look at the [languages](languages) guide. It explains how to change the language or set up multiple languages in a blog.

Let's say that the blogger changes his site's language to French (`fr`). Then, we check if a `fr.yaml` is available in the `lang` folder. If not, we'll just show English strings. However, anyone can easily add a `fr.yaml` from the Console (even someone without technical knowledge can do that). Keys don't change, only the strings.

Here's how an `fr` version of the above file will look like.

```yaml
welcome: "Bienvenue sur notre blog"
usersCount: "* utilisateurs"
byAuthor: "par {authorName}"
```

> DO NOT use nested keys in YAML language files. Keep it to simple key-value pairs.

### Language switcher {#language-switcher}

Usually, you want to render a language switcher in multi-language blogs to allow visitors to switch between languages.

```html
{% if _blog.languages | length > 1 %}
    <script>
        function toggleLanguageDropdown() {
            document.querySelector('.dropdown').classList.toggle('open');
        }
    </script>
    <div class="language-switcher">
        <a class="current-language" onclick="toggleLanguageDropdown()">{{ _lang.code }}</a>
        <div class="dropdown">
            {% for lang in _blog.languages %}
                <a
                    href="{{ lang.code | language_variant_url }}"
                    class="{% if lang.code == _lang.code %}active{% endif %}"
                >{{ lang.name }}</a>
            {% endfor %}
        </div>
    </div>
{% endif %}
```

* `{% if _blog.languages | length > 1 %}` checks if the blog has more than one language. There is no need to have a language switcher in single-language blogs.
* `_lang` is the current language. So, `{{ _lang.code }}` displays the current language code.
* `{% for lang in _blog.languages %}` loops through all the languages in the blog and renders an `<a>` element for each language inside the dropdown.
* The `language_variant_url` [helper](themes-templates#twig-helpers) filter is used to generate the URL. This filter finds out the best possible language variant of the current page. For example, if the current page is a post, this function will give the URL of the language variant of that post only if that variant exists. If not, the URL of the index page (in the given language code) will be returned.