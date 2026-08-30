<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Writing

Let's learn how to use the Hyvor Blogs Editor and publish your **first post!**

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

<h2 id="posts-pages">Posts & Pages</h2>

Hyvor Blogs supports two types of content: **posts** and **pages**. In most cases, you will be using posts. Pages are used for static content like About, Contact, etc.

**Posts**:

- are the main part of your blog, where you share your ideas, thoughts, and stories.
- appear in the index page or other collection pages
- can be created, edited, and deleted at Console → Posts
- have authors and tags

**Pages**:

- contain static information
- do not appear in the index page.
- usually is linked to in header or footer navigations.
- can create, edit, and delete pages at Console → Pages
- do not have authors or tags

**Both**:

- are uniquely identified by the **slug**
- by default have `/{slug}` permalink. See [this](/docs/routes#permalinks) if you want to change post/page permalinks, for example to include year and month in the URL.

<DocsImage src="/images/docs/writing/posts-pages-console.png" alt="Posts & Pages" width={400} />
<h2 id="editor">Editor</h2>

Hyvor Blogs comes with a rich text editor that supports inline styles and blocks.

<h3 id="inline-styles">Inline Styles</h3>

To add inline styles to text, select the text. A popup will be shown with the available options. Click on the inline style you want to add. The following inline styles are supported.

- Bold
- Italic
- Strikethrough
- Inline Code

<DocsImage src="/images/docs/writing/inline-styles.png" alt="Inline Styles" width={400} />

<h4 id="links">Links</h4>

##### Adding & Removing Links

Adding links is similar to adding [inline styles](/docs/writing#inline-styles). Select the text you want to link and then click the Link icon. Next, paste the URL in the input and hit Enter.

<DocsImage src="/images/docs/writing/link-add.gif" alt="Adding Links" />

To remove a link, select the text and click on the delete icon.

<DocsImage src="/images/docs/writing/link-removal.gif" alt="Removing Links" />

##### Links with Anchors

<!--text for anchor links-->

To add anchor links easily, follow the steps below.

1. Select the text you want to link and click on the link icon
2. Click on "Anchors"
3. Choose the anchor and click on it

<DocsImage src="/images/docs/writing/anchors.png" alt="Adding anchor links" width={400} />

<Callout type="info">
	{#snippet icon()}
		<div>💡</div>
	{/snippet}
	<p>
		Anchor list contains all the headlines of your content showing whether they have IDs or not.
	</p>
</Callout>

##### Linking Posts

To link to a post of the same blog, follow these steps.

1. Select the text you want to link and click on the link icon
2. Click on "Posts"
3. Search for the post by typing the title, and click on it

<DocsImage src="/images/docs/writing/post-link.png" alt="Linking posts" width={400} />

<h4 id="markdown-inline-styles">Markdown for Inline Styles</h4>

You can also use Markdown shortcuts to create inline styles.

<Table columns="2fr 3fr" hover>
	<TableRow head>
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

<h3 id="slash-command">Blocks</h3>

The term "blocks" is used to refer to block-like elements you can add to posts, such as paragraphs and blockquotes. Paragraphs are the basic blocks. You can create a paragraph by pressing `Enter` anywhere inside the editor.

To add other blocks, use the slash command: type slash (`/`) in a new line to open the blocks list. Use the mouse or up/down arrows to navigate through the list.

<DocsImage src="/images/docs/writing/blocks.gif" alt="Blocks" width={400} />

Hyvor Blogs supports the following blocks.

- Paragraph
- Divider
- [Heading](/docs/writing#headings)
- [Lists](/docs/writing#lists)
- [Quote](/docs/writing#quote)
- [Callout](/docs/writing#callout)
- [Image](/docs/writing#image)
- [Embed](/docs/writing#embed)
- [Link Bookmark](/docs/writing#link-bookmark)
- [Code Block](/docs/writing#code-block)
- [Custom HTML/Twig](/docs/writing#custom-html)

<h4 id="headings">Headings</h4>

HB supports headings from `<h1>` to `<h6>`. The slash command only provides two options: Large (h2) and Medium (h3). Other headings can be added using Markdown syntax in a new line.

- `#` + `space` for `h1`
- `##` + `space` for `h2`
- `###` + `space` for `h3`
- ...

<DocsImage src="/images/docs/writing/headings.gif" alt="Headings" width={400} />
<Callout type="info">
	<p>
		Please note that the reason to give <b>Large Heading</b> uses <code>{`<h2>`}</code>is that
		<code>{`<h1>`}</code> is reserved for the post title in your theme. However, you may use h1 within
		your posts if needed.
	</p>
</Callout>

<h5 id="heading-ids">Heading IDs</h5>

There are two ways to add heading IDs.

<ol>
	<li>Focus the ID input at the top of the heading and type the ID there.</li>
	<DocsImage src="/images/docs/writing/heading-id.png" alt="Heading ID" width={400} />
	<li>
		Type Markdown-style heading ID (<code>{`{#heading-id}`}</code>) at the end of the heading.
	</li>
	<DocsImage src="/images/docs/writing/heading-id-markdown.gif" alt="Heading ID in Markdown" />
</ol>

<h4 id="lists">Lists</h4>

<Table columns="2fr 3fr" hover>
	<TableRow head>
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

<DocsImage src="/images/docs/writing/lists.gif" alt="Lists" width={400} />

<h4 id="quote">Quote</h4>

Quote block is usually used to quote something, while it is also generally used make text stand out. You can create a Quote block via the [slash command](/docs/writing#slash-command) or by entering `> + space` in a new line (Markdown syntax).

<DocsImage src="/images/docs/writing/quote.gif" alt="Quote" width={400} />

You can get out of the Quote block by clicking `Enter` in a new line within the Quote block.

<h4 id="callout">Callout</h4>

Callout block is used to write text that stands out from other content in the post. You can set an emoji and background/font colors for each callout block.

<DocsImage src="/images/docs/writing/callout.gif" alt="Quote" width={400} />

<h4 id="image">Image</h4>

To add images, use [slash command](/docs/writing#slash-command) (`/` in a new line), and choose **Image**. You can add an image in one of the following ways:

<ul>
	<li>Upload from your device</li>
	<DocsImage src="/images/docs/writing/image-upload.gif" alt="Image Upload" />
	<li>Upload from a URL</li>
	<DocsImage src="/images/docs/writing/image-url.gif" alt="Image URL" />
	<li>Choose from <a href="https://unsplash.com/" rel="nofollow">Unsplash</a></li>
	<DocsImage src="/images/docs/writing/unsplash.gif" alt="Unsplash" />
	<li>Choose from Media Library</li>
	<DocsImage src="/images/docs/writing/media-lib.gif" alt="Media Library" />
</ul>

<h5 id="excalidraw">Excalidraw</h5>

Excalidraw is a tool to create diagrams and drawings. You can add Excalidraw drawings/editings in your posts.

<DocsImage src="/images/docs/writing/excalidraw.gif" alt="Excalidraw" />

For uploads, max file size is **50MB**. The following formats are supported.

- PNG - `.png`
- JPEG - `.jpg`, `.jpeg`, `.jfif`, `.pjpeg`, `.pjp`
- GIF - `.gif`
- APNG - `.apng`
- AVIF - `.avif`
- SVG - `.svg`
- WebP - `.webp`

<Callout type="info">
	<p>
		Image uploaded from device or a URL are automatically added to your <a href="/docs/media"
			>media</a
		> ensuring their availability without depending on an external service. However, images added via
		Unsplash are hosted at unsplash.com and not uploaded to blog media.
	</p>
</Callout>

The image node also allows you to:

- Add a caption
- Add ALT text
- Scale the image

<h4 id="embed">Embed</h4>

Hyvor Blogs supports embedding content from Youtube, Twitter, Instagram, Facebook, and other platforms. <a href="https://unfold.hyvor.com/" target="_blank">Hyvor Unfold</a> is used under the hood to support various platforms. You can add an embed using the [slash command](/docs/writing#slash-command) (`/` **in a new line → Embed**). Then, paste the URL you would like to embed in the input box.

<DocsImage src="/images/docs/writing/embed.gif" alt="Embed" />

See <a href="https://unfold.hyvor.com/#embed-platforms" target="_blank">supported platforms</a>.

<h4 id="link-bookmark">Link Bookmark</h4>

You can use Link Bookmark block to add a rich link previews to your posts. To insert a link bookmark, type `/` **in a new line → Link Bookmark**. Then, paste the URL you would like to embed in the input box and press Enter. Hyvor Blogs will generate a rich preview of the link using OG tags and other metadata.

<DocsImage src="/images/docs/writing/link-bookmark.gif" alt="Link Bookmark" />
<Callout type="info">
	<p>
		The URL you enter should be publicly accessible to generate a preview. For example, you cannot
		generate link bookmarks for private social media posts.
	</p>
</Callout>

<h4 id="code-block">Code Block</h4>

You can add code blocks in two ways:

- `/` **in a new line → Code Block**
- Type `` ```lang `` or `` ```js `` (with language code) in a new line and press `Enter`

<DocsImage src="/images/docs/writing/code-block.png" alt="Code Block" />

See [Syntax Highlighting](/docs/syntax-highlighting) to learn more about supported languages, supported themes, and using annotations.

<h4 id="custom-html">Custom HTML/Twig</h4>

The Custom HTML/Twig block allows you to add custom HTML or Twig code to your posts. You can use this to add custom elements to your posts. For example, you can add a custom form, a custom widget, etc. You can use Twig[variables](/docs/themes-templates#variables) in the code.

<h2 id="metadata">Post Metadata</h2>

Click the Settings button in the editor to open the post settings.

<DocsImage src="/images/docs/writing/post-meta-data.png" alt="Post Metadata" width={400} />

You can configure the following settings for a post:

- **Slug** - A unique URL-friendly identifier for the post. If you don't set a slug, Hyvor Blogs will automatically generate one based on the post title when publishing the post.
- **Publish Time** - The publish time will be added automatically when publishing the post. However, you can set a custom publish time for a post.
- **Authors** - You can add [users](/docs/users) in your blog as authors of a post. By default, the post creator is added as an author of a post. Users with [editor-level permissions](/docs/users#roles) can add or remove authors of a post. One post can have multiple authors.
- **Tags** - You can assign one or more [tags](/docs/tags) to a post.
- **Description** - A short description of the post. This will be used as the meta description of the post for search engines and social media.
- **Featured Image** - The featured image of the post. This will be used as the meta image of the post for search engines and social media.

In advanced settings, you can add the following:

<DocsImage
	src="/images/docs/writing/post-metadata-advanced.png"
	alt="Post Metadata Advanced"
	width={400}
/>

- **Canonical URL** - If you have published the same post on a different location, you can add the canonical URL of the post here.
- **Code Head** - Custom code to add right before the `</head>` tag of the post.
- **Code Foot** - Custom code to add right before the `</body>` tag of the post.

<Callout type="info">
	<p>
		See <a href="/docs/custom-code">custom code</a> documentation for more information on different ways
		of adding custom code to your blog.
	</p>
</Callout>

<h2 id="status">Post Status</h2>

A post can have one of the following statuses:

- **Draft** - Not visible to the public. Only [users](/docs/users) of your blog can see the post in the Console.
- **Scheduled** - Not visible to the public. It will be published automatically at the specified time.
- **Published** - Visible to the public.

<h3 id="publishing">Publishing</h3>

Once you have finished writing your post, you can publish it. Once published, the post will appear publicly on your blog.

**Post → Publish**

<DocsImage src="/images/docs/writing/publishing.gif" alt="Publishing" />

<h3 id="scheduling">Scheduling</h3>

You can also schedule the post at a specific date and time. Hyvor Blogs will automatically publish your post at the specified time.

**Post → Publish → Publish Later → Schedule**

<DocsImage src="/images/docs/writing/publish-schedule.gif" alt="Scheduling" />

<h3 id="unpublishing">Unpublishing</h3>

You can unpublish a published post. The post's status will be change to Draft. Therefore, it will no longer appear on the blog. You can re-publish it later.

**Post → Unpublish**

<DocsImage src="/images/docs/writing/unpublish.gif" alt="Unpublishing" />

<h3 id="deleting">Deleting</h3>

You can also permanently delete a post. Note that there is no way to restore a post after deleting. Consider Unpublishing if just want to hide the post from your blog.

**Post → Settings → Delete Post**

<DocsImage src="/images/docs/writing/delete.gif" alt="Deleting" />

<h2 id="other-guides">Other Guides</h2>
<h3 id="auto-saving">Auto-saving & Post History</h3>

If you are editing a draft, Hyvor Blogs will automatically save your post every 15 seconds if any post data (content or metadata) has been edited. You can also manually save your post by pressing **Ctrl + S**. Check the bottom right corner of the editor to see the status of the auto-saving.

<DocsImage src="/images/docs/writing/saving.gif" alt="Auto-saving" />

If the content is edited, a post history will be created. You can refer back to this history if you want to revert to a previous version of your post. A single post can have up to 25 post histories.

<h3 id="editing-published">Editing a published post</h3>

You can make changes to a published post content at any time. However, the changes will not be visible to the public until you publish the changes.

<DocsImage src="/images/docs/writing/editor.png" alt="Editor" />

1. **Update** - Publish the changes to the post. The post will be updated immediately.
2. **Discard** - Discard the changes and revert to the published version of the post.
3. **Editing published** - This message will be shown if you are editing a published post.
4. This message can be one of these:
   - **Unsaved** - The changes you have made to the post are not saved yet. Press **Ctrl + S** to save the changes. Or, it will be saved automatically in 15 seconds.
   - **Saved** - All changes are saved

<Callout type="info">
	<p>Note that metadata changes will be saved immediately and will be visible to the public.</p>
</Callout>

<h3 id="multi-language">Multi-language posts</h3>

If you have set up multiple languages for your blog, you will see the language codes at the top of the post editor. Click on a language code to switch to that language variant of the post. Each variant should be published separately. See our [languages](/docs/languages) guide, which explains everything you need to know about publishing multi-language posts.

<h2 id="seo-analysis">SEO Analysis</h2>

The SEO analysis tool in the post editor will give you suggestions to improve your post's SEO. It works based on pre-defined rules, inspired by the <a href="https://rankmath.com/kb/score-100-in-tests" rel="nofollow">Rank Math</a> WordPress plugin.

<DocsImage src="/images/docs/writing/seo.png" alt="SEO Analysis" width={400} />
<Callout type="info">
	<p><b>Important!</b></p>

	<p>
		SEO analysis is <b>merely a suggestion</b>. Getting a higher score alone will not make your
		posts rank high. There are also other factors that affect your SEO, such as backlinks, domain
		authority, etc. However, these suggestions will help you better optimize your content for search
		engines.
	</p>
</Callout>

To start analyzing your post, add a primary keyword for your post. You can also add secondary keywords. Hyvor Blogs will then analyze your post content and metadata and give you suggestions in real-time to improve SEO for your post.

These are the tests that Hyvor Blogs will run on your post:

<ul>
	<li><b>Primary keyword in the title</b></li>
	<ul>
		<li>100% if the primary keyword is at the beginning of the title</li>
		<li>75% if the primary keyword is in the first 50 characters of the title</li>
		<li>49% if the primary keyword is after the first 50 characters of the title</li>
		<li>0% if the primary keyword is not in the title</li>
	</ul>

	<li><b>Primary keyword in the description</b></li>
	<li><b>Primary keyword in the slug</b></li>
	<p>
		If the primary keyword is <code>blogging platforms</code>, we check for
		<code>blogging-platforms</code>
		in the slug. It is recommended to set a <b>short slug with hyphens</b>. In this case, the score
		will be:
	</p>
	<ul>
		<li>100% if the slug matches exactly <code>blogging-platforms</code></li>
		<li>75% if the slug contains <code>blogging-platforms</code> with other words</li>
	</ul>

	<li><b>Primary keyword in the beginning of the content</b></li>
	<p>
		If your content is longer than 300 words, the primary keyword should be in the first 10% of the
		content. If it is shorter than 300 words, it should be somewhere in the content.
	</p>

	<li><b>Content length</b></li>
	<p>(The best content length depends on the topic, which is not considered here)</p>
	<ul>
		<li>0% for less than 400 words</li>
		<li>1% for each 25 words after 400 words (2500+ words = 100%)</li>
	</ul>

	<li><b>All keywords in the content</b></li>
	<p>All keywords should be present in the post content.</p>

	<li><b>All keywords in subheadings</b></li>
	<p>Each keyword should be present in at least one subheading (h2, h3, h4, h5, h6).</p>

	<li><b>All keywords in image alt attributes</b></li>
	<p>Each keyword should be present in at least one image alt attribute.</p>

	<li><b>Keyword density</b></li>
	<p>Checks for keyword density in content (<code>keywords count / total words</code>).</p>
	<ul>
		<li>0% for less than 0.1%</li>
		<li>50% for 0.1% to 0.5%</li>
		<li>100% for 0.5% to 2.5%</li>
		<li>50% for 2.5% to 5%</li>
		<li>0% for more than 5%</li>
	</ul>

	<li><b>Slug length</b></li>
	<p>Shorter slugs are better. This test will pass if the slug is less than 50 characters.</p>

	<li><b>External links</b></li>
	<p>At least one external link should be present in the post.</p>

	<li><b>Internal links</b></li>
	<p>
		At least one internal link should be present in the post. Links to any subdomain of your main
		domain will be considered as internal links. See <a href="/docs/writing#link-types"
			>link types</a
		>
		for more information. <code>internal-blog</code>, <code>internal-domain</code>, and
		<code>internal-root-domain</code> links are considered as internal links.
	</p>

	<li><b>Images</b></li>
	<ul>
		<li>70% - 1 image</li>
		<li>80% - 2 images</li>
		<li>90% - 3 images</li>
		<li>100% - 4 or more images</li>
	</ul>

	<li><b>All images have alt attributes</b></li>
	<p>All images should have alt attributes</p>
</ul>

<h2 id="link-analysis">Link Analysis</h2>

The link analysis tool in the post editor analyzes the status of the links in your post as you write. It will show you a warning if there are any broken, risky, or redirect links in your post. It also shows you the [type of each link](/docs/writing#link-types).

<DocsImage src="/images/docs/writing/link-analysis.png" alt="Link Analysis" width={400} />
<h3 id="link-types">Link Types</h3>

Hyvor Blogs categorizes links into the following types.

<Table columns="2fr 3fr" hover>
	<TableRow head>
		<div>Link Type</div>
		<div>Description</div>
	</TableRow>

	<TableRow>
		<div><code>internal-blog</code></div>
		<div>Links to other posts/pages in your blog</div>
	</TableRow>

	<TableRow>
		<div><code>internal-domain</code></div>
		<div>Links to the same domain as your blog, but not to your blog</div>
	</TableRow>

	<TableRow>
		<div><code>internal-root-domain</code></div>
		<div>Links to any domain of the root domain, but not to your blog's domain</div>
	</TableRow>

	<TableRow>
		<div><code>external</code></div>
		<div>Links to other domains</div>
	</TableRow>

	<TableRow>
		<div><code>mail</code></div>
		<div>Mailto links (starts with <code>mailto:</code>)</div>
	</TableRow>

	<TableRow>
		<div><code>tel</code></div>
		<div>Tel links (starts with <code>tel:</code>)</div>
	</TableRow>

	<TableRow>
		<div><code>anchor</code></div>
		<div>Links to anchors in the same page (starts with <code>#</code>)</div>
	</TableRow>

	<TableRow>
		<div><code>other</code></div>
		<div>Other links (<code>ftp:</code>, <code>data:</code>, javascript, etc.)</div>
	</TableRow>
</Table>

Go to **Console → Tools → Link Analysis** to

- see an overview of all links in your blog
- view results of bi-weekly link analysis
- change email report settings

<h3 id="link-analysis-accuracy">Link Analysis Accuracy</h3>

Our link analyzer is simple: it sends HTTP requests via curl to check the status of the links. This approach allows for a fast and accurate analysis. However, some servers and firewalls may block these requests, which may result in false positives. If you find a link that is marked as broken but is actually working, you can click the ignore button to ignore the link in future analyses.

<h2 id="gpt-writing">GPT Writing</h2>

We have integrated GPT 3.5 directly into the editor to help you with AI content generation tasks such as

- Generating a blog outline
- Writing a blog post
- Writing an article
- and more...

You can use the default prompts in most cases. However, you can also customize the prompts to get better results.

<DocsImage src="/images/docs/writing/ai.gif" alt="GPT Writing" />
