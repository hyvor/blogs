# Internationalization

We do not expect you to translate themes to multiple languages, but the theme **should be translatable**. It means that all strings in the template files should be translatable.

Instead of writing this:

```html
<h1>Welcome</h1>
```

You have to write this:

```twig
<h1>{{ 'welcome' | lang }}
```

Here, `'welcome'` is the key in `en.yaml`. And, `lang` is a custom Twig filter.

### Lang folder

The `/lang` folder contains `.yaml` language files. A language file may look like this:

```yaml
welcome: Welcome
```

(You may notice the YAML key is the one we used above as the key for the Twig filter).

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

### How translations work

You may take a look at the [languages](languages) guide. It explains how to change the language or set up multiple languages.

Let's say user changes his site's language to French (`fr`). Then, we check if a `fr.yaml` is available in the `lang` folder. If not, we have no clue, we'll just show English strings. However, anyone can easily translate the strings inside a `fr.env` file (even someone without technical knowledge can do that). Keys don't change, only the string.

Here's how an `fr` version of the above file will look like.

```yaml
welcome: "Bienvenue sur notre blog"
usersCount: "* utilisateurs"
byAuthor: "par {authorName}"
```

> DO NOT use nested keys in YAML language files. Keep it to simple key-value pairs.