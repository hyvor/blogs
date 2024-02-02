<script lang="ts">
    import { DocsImage } from '@hyvor/design/marketing';
    import postsPagesImg from './posts-pages-console.png';
    import inlineStylesImg from './inline-styles.png';
    import linkAddGIF from './link-add.gif';
    import linkRemovalGIF from './link-removal.gif';
    import anchorsImg from './anchors.png';
    import postLinkImg from './post-link.png';
    import blocksImgs from './blocks.gif';
    import headingsGIF from './headings.gif';
    import headingIdImg from './heading-id.png';
    import headingIdMarkdownGIF from './heading-id-markdown.gif';
    import listsGIF from './lists.gif';
    import quoteGIF from './qoute.gif';
    import calloutGIF from './callout.gif';
	import { Callout, Table, TableRow } from '@hyvor/design/components';
	
</script>

<h1>Writing</h1>

<p>
    Let's learn how to use the Hyvor Blogs Editor and publish your <strong>first post!</strong>
</p>

<!-- <ul>
    <li><a href="#posts-pages">Posts & Pages</a></li>
    <li>
        <a href="#editor">Editor</a>
        <ul>
            <li>
                <a href="#inline">Inline Styles</a> (bold, italic, etc.)
            </li>
            <li>
                <a href="#links">Links</a>
            </li>
            <li>
                <a href="#images">Blocks</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="#post-metadata">Post Metadata</a> (authors, tag, custom code)
    </li>
    <li>
        <a href="#post-status">Post Status</a>
        <ul>
            <li><a href="#publishing">Publishing</a></li>
            <li><a href="#scheduling">Scheduling</a></li>
            <li><a href="#unpublishing">Unpublishing</a></li>
            <li><a href="#deleting">Deleting</a></li>
        </ul>
    </li>
    <li>
        <a href="#other-guides">Other Guides</a>
        <ul>
            <li><a href="#auto-saving">Auto-saving & Post History</a></li>
            <li><a href="#editing-published">Editing a published post</a></li>
            <li><a href="#multi-language">Multi-language posts</a></li>
        </ul>
    </li>
    <li><a href="#seo-analysis">SEO Analysis</a></li>
    <li><a href="#link-analysis">Link Analysis</a></li>
    <li><a href="#gpt-writing">GPT Writing</a></li>
</ul> -->

<h2 id="posts-pages">
    Posts vs Pages
</h2>

<p>
    Hyvor Blogs supports two types of content: <strong>posts</strong> and <strong>pages</strong>. In most cases, you will be using posts. Pages are used for static content like About, Contact, etc.
</p>

<p>
    <strong>Posts</strong>:
</p>

<ul>
    <li>
        are the main part of your blog, where you share your ideas, thoughts, and stories.
    </li>
    <li>
        appear in the index page or other collection pages
    </li>
    <li>
        can be created, edited, and deleted at Console → Posts
    </li>
    <li>
        have authors and tags
    </li>
</ul>

<p>
    <strong>Pages</strong>:
</p>

<ul>
    <li>
        contain static information
    </li>
    <li>
        do not appear in the index page.
    </li>
    <li>
        usually is linked to in header or footer navigations.
    </li>
    <li>
        can create, edit, and delete pages at Console → Pages
    </li>
    <li>
        do not have authors or tags
    </li>
</ul>

<p>
    <strong>Both</strong>:
</p>

<ul>
    <li>
        are uniquely identified by the <strong>slug</strong>
    </li>
    <li>
        by default have <code>{`/{slug}`}</code> permalink. See <a href="/docs/routes#permalinks">this</a> if you want to change post/page permalinks, for example to include year and month in the URL.
    </li>
</ul>

<DocsImage src={postsPagesImg} alt="Posts & Pages" width={400} />
<h2 id="editor">Editor</h2>

<p>Hyvor Blogs comes with a rich text editor that supports inline styles and blocks.</p>

<h3 id="inline">Inline Styles</h3>
<p>To add inline styles to text, select the text. A popup will be shown with the available options. Click on the inline style you want to add. The following inline styles are supported.</p>
<ul>
    <li>Bold</li>
    <li>Italic</li>
    <li>Strikethrough</li>
    <li>Inline Code</li>
</ul>

<DocsImage src={inlineStylesImg} alt="Inline Styles" width={400} />


<h3 id="links">Links</h3>

<h4>Adding & Removing Links</h4>
<p>Adding links is similar to adding <a href="/docs/writing#inline-styles">inline styles</a>. Select the text you want to link and then click the Link icon. Next, paste the URL in the input and hit Enter.</p>
<DocsImage src={linkAddGIF} alt="Adding Links" />

<p>To remove a link, select the text and click on the delete icon.</p>
<DocsImage src={linkRemovalGIF} alt="Removing Links" />

<h4>Links with Anchors</h4>
<!--text for anchor links-->
<p>To add anchor links easily, follow the steps below.</p>
<ol>
    <li>Select the text you want to link and click on the link icon</li>
    <li>Click on "Anchors"</li>
    <li>Choose the anchor and click on it</li>
</ol>

<DocsImage src={anchorsImg} alt="Adding anchor links" width={400} />

<Callout type="info">
    <div slot="icon">💡</div>
    <p>Anchor list contains all the headlines of your content showing whether they have IDs or not.</p>
</Callout>

<h4>Linking Posts</h4>
<p>To link to a post of the same blog, follow these steps.</p>
<ol>
    <li>Select the text you want to link and click on the link icon</li>
    <li>Click on "Posts"</li>
    <li>Search for the post by typing the title, and click on it</li>
</ol>

<DocsImage src={postLinkImg} alt="Linking posts" width={400} />

<h3 id="markdown-inline-styles">Markdown for Inline Styles</h3>
<p>You can also use Markdown shortcuts to create inline styles.</p>
<Table columns="2fr 3fr" hover>
    <TableRow header>
        <div>Inline Style</div>
        <div>Markdown Shortcut</div>
    </TableRow>

    <TableRow>
        <div><a href="/docs/writing#links">Link</a></div>
        <div><code>[Anchor](https://example.com)</code></div>
    </TableRow>

    <TableRow>
        <div><b>Bold</b></div>
        <div><code>**text**</code></div>
    </TableRow>

    <TableRow>
        <div><i>Italic</i></div>
        <div><code>*text*</code></div>
    </TableRow>

    <TableRow>
        <div><s>Strike</s></div>
        <div><code>~~text~~</code></div>
    </TableRow>

    <TableRow>
        <div>Super<sup>script</sup></div>
        <div><code>^text^</code></div>
    </TableRow>

    <TableRow>
        <div>Sub<sub>script</sub></div>
        <div><code>~text~</code></div>
    </TableRow>
</Table>

<h3 id="/docs/writing#blocks">Blocks</h3>
<p>The term "blocks" is used to refer to block-like elements you can add to posts, such as paragraphs and blockquotes. Paragraphs are the basic blocks. You can create a paragraph by pressing <code>Enter</code> anywhere inside the editor.</p>

<p>To add other blocks, use the slash command: type slash (<code>/</code>) in a new line to open the blocks list. Use the mouse or up/down arrows to navigate through the list.</p>

<DocsImage src={blocksImgs} alt="Blocks" width={400} />
<p>Hyvor Blogs supports the following blocks.</p>
<ul>
    <li>Paragraph</li>
    <li>Divider</li>
    <li><a href="/docs/writing#headings">Heading</a></li>
    <li><a href="/docs/writing#lists">Lists</a></li>
    <li><a href="/docs/writing#quote">Quote</a></li>
    <li><a href="/docs/writing#callout">Callout</a></li>
    <li><a href="/docs/writing#image">Image</a></li>
    <li><a href="/docs/writing#embed">Embed</a></li>
    <li><a href="/docs/writing#link-bookmark">Link Bookmark</a></li>
    <li><a href="/docs/writing#code-block">Code Block</a></li>
    <li><a href="/docs/writing#custom-html">Custom HTML/Twig</a></li>
</ul>

<h4 id="headings">Headings</h4>
<p>HB supports headings from <code>{`<h1>`}</code> to <code>{`<h6>`}</code>. The slash command only provides two options: Large (h2) and Medium (h3). Other headings can be added using Markdown syntax in a new line.</p>
<ul>
    <li><code>#</code> + <code>space</code> for <code>h1</code></li>
    <li><code>##</code> + <code>space</code> for <code>h2</code></li>
    <li><code>###</code> + <code>space</code> for <code>h3</code></li>
    <li>...</li>
</ul>

<DocsImage src={headingsGIF} alt="Headings" width={400} />

<Callout type="info">
    <p>Please note that the reason to give <b>Large Heading</b> uses <code>{`<h2>`}</code>is that <code>{`<h1>`}</code> is reserved for the post title in your theme. However, you may use h1 within your posts if needed.</p>
</Callout>

<h5 id="heading-ids">Heading IDs</h5>
<p>There are two ways to add heading IDs.</p>
<ol>
    <li>Focus the ID input at the top of the heading and type the ID there.</li>
    <DocsImage src={headingIdImg} alt="Heading ID" width={400} />
    <li>Type Markdown-style heading ID (<code>{`{#heading-id}`}</code>) at the end of the heading.</li>
    <DocsImage src={headingIdMarkdownGIF} alt="Heading ID in Markdown" />
</ol>

<h4 id="lists">Lists</h4>
<Table columns="2fr 3fr" hover>
    <TableRow header>
        <div>Action</div>
        <div>Command</div>
    </TableRow>

    <TableRow>
        <div>Create unordered list</div>
        <div><code>* + space</code> or <code>- + space</code></div>
    </TableRow>

    <TableRow>
        <div>Create ordered list</div>
        <div><code>1. + space</code>, <code>2. + space</code>,etc.</div>
    </TableRow>

    <TableRow>
        <div>New list item</div>
        <div><code>Enter</code></div>
    </TableRow>

    <TableRow>
        <div>Decrease indent</div>
        <div><code>Shift + Tab</code></div>
    </TableRow>

    <TableRow>
        <div>Increase indent</div>
        <div><code>Tab</code></div>
    </TableRow>
</Table>

<DocsImage src={listsGIF} alt="Lists" width={400} />

<h4 id="quote">Quote</h4>
<p>Quote block is usually used to quote something, while it is also generally used make text stand out. You can create a Quote block via the <a href="/docs/writing#slash-command">slash command</a> or by entering <code>{`> + space`}</code> in a new line (Markdown syntax).</p>
<DocsImage src={quoteGIF} alt="Quote" width={400} />
<p>You can get out of the Quote block by clicking <code>Enter</code> in a new line within the Quote block.</p>

<DocsImage src={calloutGIF} alt="Quote" width={400} />


