<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Editor

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
- Type ` ```lang ` or ` ```js ` (with language code) in a new line and press `Enter`

<DocsImage src="/images/docs/writing/code-block.png" alt="Code Block" />

See [Syntax Highlighting](/docs/syntax-highlighting) to learn more about supported languages, supported themes, and using annotations.

<h4 id="custom-html">Custom HTML/Twig</h4>

The Custom HTML/Twig block allows you to add custom HTML or Twig code to your posts. You can use this to add custom elements to your posts. For example, you can add a custom form, a custom widget, etc. You can use Twig[variables](/docs/themes-templates#variables) in the code.