<script lang="ts">
	import { DocsImage } from "@hyvor/design/marketing";

    import addLanguageImg from './add-language.png';
    import translatePostImg from './translate-post-variant.gif';
    import translateDataImg from './translate-data.gif';

	import { CodeBlock, Table, TableRow } from "@hyvor/design/components";
</script>

<h1>Languages</h1>

<p>
    Hyvor Blogs comes with in-built multi-language support. This guide will help you to set up languages of your blog correctly. Language settings are located at <strong>Settings &rarr; Languages</strong>.
</p>

<h2 id="primary-language">
    Primary Language
</h2>

<p>
    <strong>English (en)</strong> is the primary language for newly created blogs. If you are blogging in a different language, it is important to change the language in Language settings to tell users, browsers, and crawlers the language of your blog.
</p>

<p>
    Each language in your blog has a code, name, and a direction.
</p>

<ul>
    <li>
        <strong>Code</strong> - The language code should be a valid <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/lang" target="_blank" rel="nofollow">HTML lang attribute</a> value. Some valid examples are
        <ul>
            <li><code>en</code></li>
            <li><code>en-US</code></li>
            <li><code>en-GB</code></li>
            <li><code>fr</code></li>
            <li><code>fr-FR</code></li>
        </ul>
    </li>
    <li>
        <strong>Name</strong> - The language name is the text that explains the language code. We recommend you to write it in the native alphabet. Some themes may use the name to show a message like "This post is translated into Español, 简体中文, and Nederlands".
    </li>
    <li>
        <strong>Direction</strong> - Left-to-right or right-to-left. Setting this to RTL will change the UI according. All official themes are built with RTL support.
    </li>
</ul>

<h2 id="mutiple-languages">
    Setting up Multiple Languages
</h2>

<p>
    Everything in Hyvor Blogs is designed to support multiple languages. Most of the texts can be translated from the Console UI while some texts (like the texts in the theme) should be translated in YAML files.
</p>

<h3 id="add-language">
    Step 1: Add a Language
</h3>

<p>
    Go to <strong>Settings &rarr; Languages</strong> and click <strong>Add Language</strong> button to add a new language.
</p>

<DocsImage src={addLanguageImg} alt="Add Language" />

<p>
    When you add a new language, all posts will be translatable to that language. The non-primary languages will have index pages, for example, <code>/fr</code> for French. All <a href="/docs/routes">routes of your blog</a> will be available in the new language in the same format.
</p>


<h3 id="translate-posts">
    Step 2: Translate Posts
</h3>

<p>
    In the post editor, you will now see an option to switch between languages. When you switch to a new language for the first time in a post, a new draft "variant" will be created.
</p>

<DocsImage src={translatePostImg} alt="Translate post - create draft variant" />

<p>
    Then, you can translate your post content, title, slug, and description to the new language. If you use the slug <code>bonjour-monde</code> for the French variant, the URL of the post will be <code>/fr/bonjour-monde</code>.
</p>

<h3 id="translate-data">
    Step 3: Translate Data
</h3>

<p>
    All data in your blog can be translated. For example, the blog name, description, navigation anchors, etc. Here are the places you can translate data.
</p>

<Table columns="1fr 1fr">
    <TableRow head>
        <div>Data</div>
        <div>Where to translate</div>
    </TableRow>
    <TableRow>
        <div>
            Blog name, description
        </div>
        <div>
            Settings → General
        </div>
    </TableRow>
    <TableRow>
        <div>
            Navigation anchors
        </div>
        <div>
            Settings → Navigation
        </div>
    </TableRow>
    <TableRow>
        <div>
            Post slug, description, title
        </div>
        <div>
            Post editor
        </div>
    </TableRow>
    <TableRow>
        <div>
            Author name, bio
        </div>
        <div>
            Settings → Users
        </div>
    </TableRow>
    <TableRow>
        <div>
            Tag name, description
        </div>
        <div>
            Settings → Tags
        </div>
    </TableRow>
</Table>

<p>
    Translatable settings will have a UI like this. First, create a variant, then translate the data.
</p>

<DocsImage src={translateDataImg} alt="Translate data" />

<h3 id="translate-theme">
    Step 4: Translate Theme
</h3>

<p>
    All official themes are built with multi-language support. You can translate the theme texts in YAML files.
</p>

<ol>
    <li>
        Go to the <strong>Theme &rarr; lang</strong> section in the Console. All language files are located in the <code>lang</code> folder. <code>en.yaml</code> is the default language file.
    </li>
    <li>
        Copy the contents of the <code>en.yaml</code> file.
    </li>
    <li>
        Create a new file with the language code of the language you want to translate to. For example, <code>fr.yaml</code> for French. Then, paste the copied contents to the new file.
    </li>
    <li>
        Finally, start translating the texts.
        <ul>
            <li>
                YAML files have <code>key: value</code> pairs.
            </li>
            <li>
                Keep the keys as they are. Only translate the values.
            </li>
            <li>
                <code>*</code> and <code>{`{key}`}</code> are placeholders. Do not translate them.
            </li>
            <li>
                You may wrap the value in double quotes (") if you want to use special characters like <code>:</code>, <code>*</code>, etc.
            </li>
        </ul>
    </li>
</ol>

<p>
    Example:
</p>

<p>
    <code>en.yaml</code>
</p>

<CodeBlock language="yaml" code={`
    comments: Comments
    posts_num_multi: "* Posts"
    author: "by {name}"
`} />


<p>
    <code>fr.yaml</code>
</p>

<CodeBlock language="yaml" code={`
    comments: Commentaires
    posts_num_multi: "* Articles"
    author: "par {name}"
`} />

<h2 id="technical-seo">
    Technical SEO
</h2>

<p>
    Here are some under the hood works that Hyvor Blogs do to make sure search engine robots understand your multi-language pages. You don't need to do anything for these.
</p>

<p>
    HB adds the lang attribute to the <code>{`<html` + `>`}</code> tag in all pages using the language code you set (this is why using the correct language codes are important).
</p>

<CodeBlock code={`<` + `html lang="en">`} />

<p>
    In addition, HB will add <code>hreflang</code> alternate tags. For example, if you have three languages (<code>en</code>, <code>fr</code>, <code>es</code>), the en index page (/) will have these tags.
</p>

<CodeBlock language="html" code={`
    <link rel="alternate" href="https://yourblog.com/fr" hreflang="fr" />
    <link rel="alternate" href="https://yourblog.com/es" hreflang="es" />
`} />

<p>
    For posts, we will add these alternate tags <strong>only if</strong> the translated variants are <strong>published</strong>.
</p>