<script lang="ts">
	import { Callout, CodeBlock } from '@hyvor/design/components';
</script>

<h1 id="internationalization">Internationalization</h1>

<p>
	We do not expect you to translate themes to multiple languages, but the theme should be
	translatable. It means that all strings in the template files <b>should be translatable</b>.
	This is a requirement if you are planning to
	<a href="https://blogs.hyvor.com/docs/themes-publishing">publish</a> the theme.
</p>

<Callout type="info">
	<p>If you are creating a private theme for a single language blog, you may skip this part.</p>
</Callout>

<p>Internationalization is easy. Instead of writing this:</p>

<CodeBlock
	language="html"
	code={`
<h1>Welcome</h1>
`}
/>

<p>You have to write this:</p>

<CodeBlock
	language="html"
	code={`
<h1>{{ 'welcome' | lang }}</h1>
`}
/>

<p>
	Here, <code>'welcome'</code> is a key in <code>en.yaml</code>. And, <code>lang</code> is a
	custom Twig filter defined by HB. HB will display the correct language based on the
	<a href="/docs/languages">language of the user's blog</a>.
</p>

<h2 id="lang-folder">Lang folder</h2>

<p>
	The <code>/lang</code> folder contains <code>.yaml</code> language files. A language file may look
	like this:
</p>
<CodeBlock
	language="yaml"
	code={`
welcome: Welcome
`}
/>

<h2 id="english-required">English is required</h2>

<p>
	English (<code>en.yaml</code>) is the default language and it is required. You may also define
	other languages. Language codes should be
	<a href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" rel="nofollow"
		>ISO 639-1 Codes</a
	>.
</p>
<p>Let's see another example:</p>

<CodeBlock
	language="yaml"
	code={`
welcome: "Welcome to our blog"
usersCount: "* users"
byAuthor: "by {authorName}"
`}
/>

<p>
	In Twig templates, use the <code>lang</code> filter to render these strings with placeholders replaced.
</p>

<CodeBlock
	language="html"
	code={`
<h1>
    {{ 'welcome' | lang }}
</h1>

<p>{{ 'byAuthor' | lang(authorName=_author.name) }}</p>

<p>{{ 'usersCount' | lang(2) }}</span>
`}
/>

<p>As you can see there are two placeholders types:</p>

<ul>
	<li><code>*</code></li>
	<ul>
		<li>Use if the string only has one input (in most cases, a number)</li>
	</ul>
	<li><code>named</code></li>

	<ul>
		<li>example: <code>{`{authorName}`}</code></li>
		<li>
			Use if the string has more inputs or if the inputs are data that can be described in
			name.
		</li>
		<li>You can have multiple named placeholders in the string.</li>
		<li>
			In the lang Twig filter, use <a
				href="https://twig.symfony.com/doc/3.x/templates.html#named-arguments"
				rel="nofollow">named arguments</a
			> to fill placeholders with real data.
		</li>
	</ul>
</ul>

<h2 id="lang-by-number">Conditional strings based on a number</h2>
<p>
	Sometimes you may want to display a different message when a number is zero, one, or more than
	one. Instead writing a bunch of if conditions, you may use the <code>lang_by_number</code> custom
	Twig filter.
</p>

<CodeBlock
	language="yaml"
	code={`
# en.yaml
posts_num_zero: No Posts
posts_num_one: 1 Post
posts_num_multi: "* Posts"
`}
/>

<CodeBlock
	language="yaml"
	code={`
Number of posts: 
{{ _pagination.total | lang_by_number(
    zero="posts_num_zero",
    one="posts_num_one",
    multi="posts_num_multi"
) }}
`}
/>

<p>
	<code>{`_pagination.total`}</code> is a number. The <code>*</code> in
	<code>posts_num_multi</code> will be replaced by the given number.
</p>

<h2 id="how-tranlsations-work">How translations work</h2>
<p>
	You may take a look at the <a href="/docs/languages">languages</a> guide. It explains how to change
	the language or set up multiple languages in a blog.
</p>
<p>
	Let's say that the blogger changes his site's language to French (<code>fr</code>). Then, we
	check if a <code>fr.yaml</code> is available in the <code>lang</code> folder. If not, we'll just
	show English strings. However, anyone can easily add a <code>fr.yaml</code> from the Console (even
	someone without technical knowledge can do that). Keys don't change, only the strings.
</p>

<p>Here's how an <code>fr</code> version of the above file will look like.</p>

<CodeBlock
	language="yaml"
	code={`
welcome: "Bienvenue sur notre blog"
usersCount: "* utilisateurs"
byAuthor: "par {authorName}"
`}
/>

<Callout type="info">
	<p>DO NOT use nested keys in YAML language files. Keep it to simple key-value pairs.</p>
</Callout>

<h2 id="language-switcher">Language switcher</h2>

<p>
	Usually, you want to render a language switcher in multi-language blogs to allow visitors to
	switch between languages.
</p>

<CodeBlock
	language="html"
	code={`
{% if _blog.languages | length > 1 %}
<` +
		`script>
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
`}
/>

<ul>
	<li>
		<code>{`{% if _blog.languages | length > 1 %}`}</code> checks if the blog has more than one language.
		There is no need to have a language switcher in single-language blogs.
	</li>
	<li>
		<code>_lang</code> is the current language. So, <code>{`{{ _lang.code }}`}</code> displays the
		current language code.
	</li>
	<li>
		<code>{`{% for lang in _blog.languages %}`}</code> loops through all the languages in the
		blog and renders an <code>{`<a>`}</code> element for each language inside the dropdown.
	</li>
	<li>
		The <code>{`language_variant_url`}</code>
		<a href="/docs/themes-templates#twig-helpers">helper</a> filter is used to generate the URL. This
		filter finds out the best possible language variant of the current page. For example, if the current
		page is a post, this function will give the URL of the language variant of that post only if that
		variant exists. If not, the URL of the index page (in the given language code) will be returned.
	</li>
</ul>
