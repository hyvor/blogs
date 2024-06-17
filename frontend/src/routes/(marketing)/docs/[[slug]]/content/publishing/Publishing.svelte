<script lang="ts">
    import { Callout, CodeBlock, Table, TableRow, Checkbox, InputGroup} from "@hyvor/design/components";
</script>

<h1 id="publishing-themes">Publishing Themes</h1>

<p>Are you ready to publish your newly built theme to our <a href="/themes">themes list</a>? To do that, your theme should be developed within a fork of our <a href="https://github.com/hyvor/hyvor-blogs-themes">hyvor-blogs-themes</a> repository. After everything is completed, send us a pull request to the <code>main</code> branch. If merged, your theme will be automatically added to our theme list, and other bloggers can install it easily.</p>
<Callout type="info">
    <p>All themes in our themes list are free. If you wish to create a paid theme, you have to sell it outside our platform, and users can upload the ZIP from the Console to install it.</p>
</Callout>

<h2 id="checklist">Checklist</h2>

<p>All the following requirements should be met in order to publish a theme to our official themes list.</p>

<div class="checklist-main"><Checkbox>These files should be added:</Checkbox></div>
<div class="child-checkbox">
    <InputGroup>
        <Checkbox>index.twig</Checkbox>
        <Checkbox>post.twig</Checkbox>
        <Checkbox>tag.twig</Checkbox>
        <Checkbox>author.twig</Checkbox>
        <Checkbox>404.twig</Checkbox>
    </InputGroup>
</div>

<div class="checklist-main"><Checkbox>Supports multi-languages (has a language switcher)</Checkbox></div>

<div class="checklist-main"><Checkbox>Supports both light and dark color modes</Checkbox></div>

<div class="checklist-main"><Checkbox>Respects the blog's color mode settings</Checkbox></div>

<div class="checklist-main"><Checkbox>Pagination</Checkbox></div>

<div class="checklist-main" id="mult-line">
    <div class="checklist-main-checkbox"><Checkbox></Checkbox></div>

    <div class="checklist-main-text">
        Blog search must be implemented at least with post searching. Optionally, you can add search for tags and authors.
    </div>
</div>

<div class="checklist-main" id="mult-line">
    <div class="checklist-main-checkbox"><Checkbox></Checkbox></div>

    <div class="checklist-main-text">
        All posts should have links to translated versions of them (if available). Ex: "This post is also available in..." or "Translations: ..."
    </div>
</div>

<div class="checklist-main"><Checkbox>All <a href="/docs/themes-templates#placeholders">placeholders</a> are added</Checkbox></div>
    <div class="child-checkbox">
        <InputGroup>
            <Checkbox><code>_head</code></Checkbox>
            <Checkbox><code>_foot</code></Checkbox>
            <Checkbox><code>_comments</code></Checkbox>
            <Checkbox><code>_newsletter</code></Checkbox>
        </InputGroup>
    </div>

<div class="checklist-main"><Checkbox><code>_comments</code> should only be added to posts, not pages.</Checkbox></div>

<div class="checklist-main"><Checkbox><code>_comments</code> and <code>_newsletter</code> blocks should not be displayed if the value of each is empty.</Checkbox></div>

<div class="checklist-main" id="mult-line">
    <div class="checklist-main-checkbox"><Checkbox></Checkbox></div>

    <div class="checklist-main-text">
        If the blog has a logo (<code>_blog.logo_url</code>), the logo should be shown in the header linking the logo to the blog homepage.
    </div>
</div>

<div class="checklist-main"><Checkbox><a href="/docs/themes-internationalization">Internationalized</a></Checkbox></div>

<div class="checklist-main"><Checkbox><code>{`<html lang="{{ _lang.code }}" dir="{{ _lang.direction }}"></html>`}</code> is added</Checkbox></div>

<div class="checklist-main"><Checkbox>Supports RTL languages. See <a href="/docs/themes-publishing#rtl">RTL Support</a> below.</Checkbox></div>

<div class="checklist-main"><Checkbox>Configurations are added for colors, fonts, etc. See <a href="/docs/themes-publishing#config">Config</a> section.</Checkbox></div>

<div class="checklist-main" id="mult-line">
    <div class="checklist-main-checkbox"><Checkbox></Checkbox></div>
    <div class="checklist-main-text">
        Configuration definitions (<code>config.def.yaml</code>) are added. See <a href="/docs/themes-config#config-def">Configuration -> Config Definitions</a> page. Use the <a href="/config">config tool</a> to validate <code>config.def.yaml</code>.
    </div>
</div>

<div class="checklist-main"><Checkbox>YAML files should use 2 spaces per indentation (not tabs, not 4 spaces).</Checkbox></div>

<div class="checklist-main"><Checkbox>Mobile responsive</Checkbox></div>

<div class="checklist-main" id="mult-line">
    <div class="checklist-main-checkbox"><Checkbox></Checkbox></div>
    <div class="checklist-main-text">
        Featured posts may have some unique UI in the index page (ex: a pinned/star icon)
    </div>
</div>

<div class="checklist-main"><Checkbox>Content styles are added. See Content styles section below.</Checkbox></div>

<div class="checklist-main" id="mult-line">
    <div class="checklist-main-checkbox"><Checkbox></Checkbox></div>
    <div class="checklist-main-text">
        All assets (JS, fonts, etc.) should be added in the <code>assets</code> folder. Do not load assets from external sources like Google Fonts.
    </div>
</div>

<div class="checklist-main" id="mult-line">
    <div class="checklist-main-checkbox"><Checkbox></Checkbox></div>
    <div class="checklist-main-text">
        Should support the blog's social media links (shows an icon or link to the social media profile if the link is available)
    </div>
</div>
<div class="child-checkbox">
    <InputGroup>
        <Checkbox>Facebook</Checkbox>
        <Checkbox>Twitter</Checkbox>
        <Checkbox>Linkedin</Checkbox>
        <Checkbox>Youtube</Checkbox>
        <Checkbox>TikTok</Checkbox>
        <Checkbox>Instagram</Checkbox>
        <Checkbox>Github</Checkbox>
    </InputGroup>
</div>

<div class="checklist-main" id="mult-line">
    <div class="checklist-main-checkbox"><Checkbox></Checkbox></div>
    <div class="checklist-main-text">
        Should support branding.
    </div>
</div>

<h2 id="config">Config</h2>

<p>As explained in the <a href="/docs/themes-config">configurations</a> page, the following configurations are required when publishing your theme.</p>
<CodeBlock language="yaml" code={`
THEME_NAME: my-theme
THEME_VERSION: 1.0.0
`}  />

<p>The following configurations are recommended for all published themes.</p>

<CodeBlock language="yaml" code={`
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
`}  />

<h2 id="rtl">RTL Support</h2>
<p>All published themes should support RTL (right-to-left) languages. Follow these tips to make sure your theme supports RTL.</p>

<ul>
    <li>Add <code>{`dir="{{ _lang.direction }}"`}</code> to the <code>{`<html>`}</code> tag</li>
    <li>Use direction-aware CSS properties when adding horizontal padding, margin, and left/right borders</li>
</ul>

<Table columns="3fr 3fr" hover>
    <TableRow head>
        <div>Do not use</div>
        <div>Use this</div>
    </TableRow>

    <TableRow>
        <div><code>padding-left</code></div>
        <div><code>padding-inline-start</code></div>
    </TableRow>

    <TableRow>
        <div><code>padding-right</code></div>
        <div><code>padding-inline-end</code></div>
    </TableRow>

    <TableRow>
        <div><code>margin-left</code></div>
        <div><code>margin-inline-start</code></div>
    </TableRow>

    <TableRow>
        <div><code>margin-right</code></div>
        <div><code>margin-inline-end</code></div>
    </TableRow>

    <TableRow>
        <div><code>border-left</code></div>
        <div><code>border-inline-start</code></div>
    </TableRow>

    <TableRow>
        <div><code>border-right</code></div>
        <div><code>border-inline-end</code></div>
    </TableRow>
</Table>

<ul>
    <li>Make sure absolute/fixed positioned elements are positioned correctly in RTL mode</li>
    <li><code>{`<pre><code>`}</code> blocks should have <code>direction: ltr</code> CSS</li>
    <li>Make sure to add an RTL language to your DEV blog and test RTL support</li>
</ul>


<h2 id="content-styles">Content Styles</h2>

<p>All published themes should nicely style all the blocks in the "Content Style Guide" post in your DEV blog. In addition to styling them, follow these guidelines to avoid common but subtle UX issues.</p>

<h3 id="heading-anchors">1. Heading Anchors</h3>

<p>We automatically add anchors to headings that have an <code>id</code> attribute.</p>
<p>HTML without ID:</p>

<CodeBlock language="html" code={`
<h1>My Heading</h1>
`}  />

<p>HTML with ID:</p>
<CodeBlock language="html" code={`
<h1 id="heading-anchor">
    <a href="#heading-anchor" class="heading-anchor">
        My Heading
    </a>
</h1>
`}  />

<p>These heading anchor should be <b>styled differently</b> from other links. For example, you can add a <code>#</code> or an SVG image (via <code>background-image</code>) before the anchor text.</p>
<CodeBlock language="css" code={`
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
`}  />


<h3 id="code-blocks">2. Code Blocks</h3>

<ul>
    <li>Code blocks should have <code>{`tab-size: 4`}</code></li>
    <li>Code blocks should have <code>direction: ltr</code></li>
    <li>Line numbers should be absolutely positioned</li>
    <li>Add left padding when line numbers are enabled (check for <code>.has-line-numbers</code>).</li>
</ul>

<p>And, line numbers should be absolutely positioned. Also, add left padding when line numbers are enabled (check for <code>.has-line-numbers</code>).</p>

<CodeBlock language="css" code={`
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
`}  />


<h3 id="tables">3. Tables</h3>

<ul>
    <li><code>.table-container</code> should have <code>overflow-x: auto</code> to make sure the table is scrollable on mobile devices</li>
</ul>
<CodeBlock language="css" code={`
.table-container {
    overflow-x: auto;
}
`}  />

<h3 id="paragraphs">4. Paragraphs</h3>
<p>Margins must be handled carefully for paragraphs inside lists, tables, and blockquotes. The following works well in most cases.</p>

<CodeBlock language="css" code={`
li {
    p {
        margin-top: 0;
        &:last-child {
            margin-bottom: 0;
        }
    }
}
`}  />

<h2 id="versioning">Versioning</h2>

<p>The first version of the theme should be <code>1.0.0</code>. After that you can increment the version number according to the changes you make. For a patch (ex: bug fix), you can use <code>1.0.1</code>, <code>1.0.2</code>, etc. For a minor change, you can use <code>1.1.0</code>, <code>1.2.0</code>, etc. Unlike other software, themes do not have significant changes breaking changes. Therefore, we do not think you will ever need a major version change.</p>

<h2 id="changelog">Changelog</h2>
<p>Add a <code>CHANGELOG.md</code> file to your theme folder and add the changes for each version. See <a href="https://keepachangelog.com/en/1.1.0/" rel="nofollow">keepachangelog.com</a> to learn how to write a changelog.</p>

<p>Once everything is completed, send us a pull request to <a href="https://github.com/hyvor/hyvor-blogs-themes">hyvor-blogs-themes</a> repository. When the PR is merged, the themes list will automatically update with your new theme.</p>








 <style lang="scss">
    .checklist-main {
        margin-bottom: 15px;
        margin-top: 15px;
    }

    .child-checkbox {
        margin-top: 5px;
        margin-left: 30px;
    }

    .checklist-main :global(.placeholder) {
        flex-shrink: 0;
    }

    #mult-line {
        display: flex;
        align-items: flex-start;
    }

    #mult-line .checklist-main-checkbox {
        margin-right: 8px;
    }
 </style>

