<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Table, TableRow } from '@hyvor/design/components';
</script>

# Languages

Hyvor Blogs comes with in-built multi-language support. This guide will help you to set up languages of your blog correctly. Language settings are located at **Settings &rarr; Languages**.

<h2 id="primary-language">Primary Language</h2>

**English (en)** is the primary language for newly created blogs. If you are blogging in a different language, it is important to change the language in Language settings to tell users, browsers, and crawlers the language of your blog.

Each language in your blog has a code, name, and a direction.

- **Code** - The language code should be a valid <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/lang" target="_blank" rel="nofollow">HTML lang attribute</a> value. Some valid examples are
  - `en`
  - `en-US`
  - `en-GB`
  - `fr`
  - `fr-FR`
- **Name** - The language name is the text that explains the language code. We recommend you to write it in the native alphabet. Some themes may use the name to show a message like "This post is translated into Español, 简体中文, and Nederlands".
- **Direction** - Left-to-right or right-to-left. Setting this to RTL will change the UI according. All official themes are built with RTL support.

<h2 id="mutiple-languages">Setting up Multiple Languages</h2>

Everything in Hyvor Blogs is designed to support multiple languages. Most of the texts can be translated from the Console UI while some texts (like the texts in the theme) should be translated in YAML files.

<h3 id="add-language">Step 1: Add a Language</h3>

Go to **Settings &rarr; Languages** and click **Add Language** button to add a new language.

<DocsImage src="/images/docs/languages/add-language.png" alt="Add Language" />

When you add a new language, all posts will be translatable to that language. The non-primary languages will have index pages, for example, `/fr` for French. All [routes of your blog](/docs/routes) will be available in the new language in the same format.

<h3 id="translate-posts">Step 2: Translate Posts</h3>

In the post editor, you will now see an option to switch between languages. When you switch to a new language for the first time in a post, a new draft "variant" will be created.

<DocsImage
	src="/images/docs/languages/translate-post-variant.gif"
	alt="Translate post - create draft variant"
/>

Then, you can translate your post content, title, slug, and description to the new language. If you use the slug `bonjour-monde` for the French variant, the URL of the post will be `/fr/bonjour-monde`.

<h3 id="translate-data">Step 3: Translate Data</h3>

All data in your blog can be translated. For example, the blog name, description, navigation anchors, etc. Here are the places you can translate data.

<Table columns="1fr 1fr">
	<TableRow head>
		<div>Data</div>
		<div>Where to translate</div>
	</TableRow>
	<TableRow>
		<div>Blog name, description</div>
		<div>Settings → General</div>
	</TableRow>
	<TableRow>
		<div>Navigation anchors</div>
		<div>Settings → Navigation</div>
	</TableRow>
	<TableRow>
		<div>Post slug, description, title</div>
		<div>Post editor</div>
	</TableRow>
	<TableRow>
		<div>Author name, bio</div>
		<div>Settings → Users</div>
	</TableRow>
	<TableRow>
		<div>Tag name, description</div>
		<div>Settings → Tags</div>
	</TableRow>
</Table>

Translatable settings will have a UI like this. First, create a variant, then translate the data.

<DocsImage src="/images/docs/languages/translate-data.gif" alt="Translate data" />

<h3 id="translate-theme">Step 4: Translate Theme</h3>

All official themes are built with multi-language support. You can translate the theme texts in YAML files.

1. Go to the **Theme &rarr; lang** section in the Console. All language files are located in the `lang` folder. `en.yaml` is the default language file.
2. Copy the contents of the `en.yaml` file.
3. Create a new file with the language code of the language you want to translate to. For example, `fr.yaml` for French. Then, paste the copied contents to the new file.
4. Finally, start translating the texts.
   - YAML files have `key: value` pairs.
   - Keep the keys as they are. Only translate the values.
   - `*` and `{key}` are placeholders. Do not translate them.
   - You may wrap the value in double quotes (") if you want to use special characters like `:`, `*`, etc.

Example:

`en.yaml`

```yaml
comments: Comments
posts_num_multi: "* Posts"
author: "by {name}"
```

`fr.yaml`

```yaml
comments: Commentaires
posts_num_multi: "* Articles"
author: "par {name}"
```

<h2 id="technical-seo">Technical SEO</h2>

Here are some under the hood works that Hyvor Blogs do to make sure search engine robots understand your multi-language pages. You don't need to do anything for these.

HB adds the lang attribute to the `<html>` tag in all pages using the language code you set (this is why using the correct language codes are important).

```html
<html lang="en">
```

In addition, HB will add `hreflang` alternate tags. For example, if you have three languages (`en`, `fr`, `es`), the en index page (/) will have these tags.

```html
<link rel="alternate" href="https://yourblog.com/fr" hreflang="fr" />
<link rel="alternate" href="https://yourblog.com/es" hreflang="es" />
```

For posts, we will add these alternate tags **only if** the translated variants are **published**.
