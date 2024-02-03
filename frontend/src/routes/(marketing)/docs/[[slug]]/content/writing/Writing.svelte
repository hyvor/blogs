<script lang="ts">
    import { Docs, DocsImage } from '@hyvor/design/marketing';
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
    import calloutGIF from './callout.gif';
    import qutoeGIF from './quote.gif';
    import imageUploadGIF from './image-upload.gif';
    import imageUrlGIF from './image-url.gif';
    import unsplashGIF from './unsplash.gif';
    import mediaLibGIF from './media-lib.gif';
    import excalidrawGIF from './excalidraw.gif';
    import embedGIF from './embed.gif';
    import linkBookmarkGIF from './link-bookmark.gif';
    import codeBlockImg from './code-block.png';
    import postMetadataImg from './post-meta-data.png';
    import postMetaDataAdvancedImg from './post-metadata-advanced.png';
    import publishingGIF from './publishing.gif';
    import publishScheduleGIF from './publish-schedule.gif';
    import unpublishGIF from './unpublish.gif';
    import deleteGIF from './delete.gif';
    import savingGIF from './saving.gif';
    import editorImg from './editor.png';
    import SEOImg from './seo.png';
    import linkAnalysisImg from './link-analysis.png';
    import AIGIF from './ai.gif';
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
    Posts & Pages
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


<h4 id="links">Links</h4>

<h5>Adding & Removing Links</h5>
<p>Adding links is similar to adding <a href="/docs/writing#inline-styles">inline styles</a>. Select the text you want to link and then click the Link icon. Next, paste the URL in the input and hit Enter.</p>
<DocsImage src={linkAddGIF} alt="Adding Links" />

<p>To remove a link, select the text and click on the delete icon.</p>
<DocsImage src={linkRemovalGIF} alt="Removing Links" />

<h5>Links with Anchors</h5>
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

<h5>Linking Posts</h5>
<p>To link to a post of the same blog, follow these steps.</p>
<ol>
    <li>Select the text you want to link and click on the link icon</li>
    <li>Click on "Posts"</li>
    <li>Search for the post by typing the title, and click on it</li>
</ol>

<DocsImage src={postLinkImg} alt="Linking posts" width={400} />

<h4 id="markdown-inline-styles">Markdown for Inline Styles</h4>
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
<DocsImage src={qutoeGIF} alt="Quote" width={400} />
<p>You can get out of the Quote block by clicking <code>Enter</code> in a new line within the Quote block.</p>

<h4 id="callout">Callout</h4>
<p>Callout block is used to write text that stands out from other content in the post. You can set an emoji and background/font colors for each callout block.</p>
<DocsImage src={calloutGIF} alt="Quote" width={400} />

<h4 id="image">Image</h4>
<p>To add images, use <a href="/docs/writing#slash-command">slash command</a> (<code>/</code> in a new line), and choose <b>Image</b>. You can add an image in one of the following ways:</p>
<ul>
    <li>Upload from your device</li>
    <DocsImage src={imageUploadGIF} alt="Image Upload" />
    <li>Upload from a URL</li>
    <DocsImage src={imageUrlGIF} alt="Image URL" />
    <li>Choose from <a href="https://unsplash.com/" rel="nofollow">Unsplash</a></li>
    <DocsImage src={unsplashGIF} alt="Unsplash" />
    <li>Choose from Media Library</li>
    <DocsImage src={mediaLibGIF} alt="Media Library" />
</ul>

<h5 id="excalidraw">Excalidraw</h5>
<p>Excalidraw is a tool to create diagrams and drawings. You can add Excalidraw drawings/editings in your posts.</p>
<DocsImage src={excalidrawGIF} alt="Excalidraw" />

<p>For uploads, max file size is <b>50MB</b>. The following formats are supported.</p>
<ul>
    <li>PNG - <code>.png</code></li>
    <li>JPEG - <code>.jpg</code>, <code>.jpeg</code>, <code>.jfif</code>, <code>.pjpeg</code>, <code>.pjp</code></li>
    <li>GIF - <code>.gif</code></li>
    <li>APNG - <code>.apng</code></li>
    <li>AVIF - <code>.avif</code></li>
    <li>SVG - <code>.svg</code></li>
    <li>WebP - <code>.webp</code></li>
</ul>

<Callout type = "info">
    <p>Image uploaded from device or a URL are automatically added to your <a href="/docs/media">media</a> ensuring their availability without depending on an external service. However, images added via Unsplash are hosted at unsplash.com and not uploaded to blog media.</p>
</Callout>

<p>The image node also allows you to:</p>
<ul>
    <li>Add a caption</li>
    <li>Add ALT text</li>
    <li>Scale the image</li>
</ul>

<h4 id="embed">Embed</h4>
<p>Hyvor Blogs supports embedding content from 1900+ online platforms. We use <a href="https://iframely.com/" rel="nofollow">iframely</a> under the hood to support various platforms. You can add an embed using the <a href="/docs/writing#slash-command">slash command</a> (<code>/</code> <b>in a new line → Embed</b>). Then, paste the URL you would like to embed in the input box.</p>
<DocsImage src={embedGIF} alt="Embed" />

<h4 id="link-bookmark">Link Bookmark</h4>
<p>You can use Link Bookmark block to add a rich link previews to your posts. To insert a link bookmark, type <code>/</code> <b>in a new line → Link Bookmark</b>. Then, paste the URL you would like to embed in the input box and press Enter. Hyvor Blogs will generate a rich preview of the link using OG tags and other metadata.</p>
<DocsImage src={linkBookmarkGIF} alt="Link Bookmark" />
<Callout type="info">
    <p>The URL you enter should be publicly accessible to generate a preview. For example, you cannot generate link bookmarks for private social media posts.</p>
</Callout>

<h4 id="code-block">Code Block</h4>
<p>You can add code blocks in two ways:</p>
<ul>
    <li><code>/</code> <b>in a new line → Code Block</b></li>
    <li>Type <code>```lang</code> or <code>```js</code> (with language code) in a new line and press <code>Enter</code></li>
</ul>
<DocsImage src={codeBlockImg} alt="Code Block" />
<p>See <a href="/docs/syntax-highlighting">Syntax Highlighting</a> to learn more about supported languages, supported themes, and using annotations.</p>

<h4 id="custom-html">Custom HTML/Twig</h4>
<p>The Custom HTML/Twig block allows you to add custom HTML or Twig code to your posts. You can use this to add custom elements to your posts. For example, you can add a custom form, a custom widget, etc. You can use Twig<a href="/docs/themes-templates#variables">variables</a> in the code.</p>

<h2 id="metadata">Post Metadata</h2>
<p>Click the Settings button in the editor to open the post settings.</p>
<DocsImage src={postMetadataImg} alt="Post Metadata" width={400} />
<p>You can configure the following settings for a post:</p>
<ul>
    <li><b>Slug</b> - A unique URL-friendly identifier for the post. If you don't set a slug, Hyvor Blogs will automatically generate one based on the post title when publishing the post.</li>
    <li><b>Publish Time</b> - The publish time will be added automatically when publishing the post. However, you can set a custom publish time for a post.</li>
    <li><b>Authors</b> - You can add <a href="/docs/users">users</a> in your blog as authors of a post. By default, the post creator is added as an author of a post. Users with <a href="/docs/users#roles">editor-level permissions</a> can add or remove authors of a post. One post can have multiple authors.</li>
    <li><b>Tags</b> - You can assign one or more <a href="/docs/tags">tags</a> to a post.</li>
    <li><b>Description</b> - A short description of the post. This will be used as the meta description of the post for search engines and social media.</li>
    <li><b>Featured Image</b> - The featured image of the post. This will be used as the meta image of the post for search engines and social media.</li>
</ul>

<p>In advanced settings, you can add the following:</p>

<DocsImage src={postMetaDataAdvancedImg} alt="Post Metadata Advanced" width={400} />
<ul>
    <li><b>Canonical URL</b> - If you have published the same post on a different location, you can add the canonical URL of the post here.</li>
    <li><b>Code Head</b> - Custom code to add right before the <code>{`</head>`}</code> tag of the post.</li>
    <li><b>Code Foot</b> - Custom code to add right before the <code>{`</body>`}</code> tag of the post.</li>
</ul>

<Callout type="info">
    <p>See <a href="/docs/custom-code">custom code</a> documentation for more information on different ways of adding custom code to your blog.</p>
</Callout>

<h2 id="status">Post Status</h2>
<p>A post can have one of the following statuses:</p>
<ul>
    <li><b>Draft</b> - Not visible to the public. Only <a href="/docs/users">users</a> of your blog can see the post in the Console.</li>
    <li><b>Scheduled</b> - Not visible to the public. It will be published automatically at the specified time.</li>
    <li><b>Published</b> - Visible to the public.</li>
</ul>

<h3 id="publishing">Publishing</h3>
<p>Once you have finished writing your post, you can publish it. Once published, the post will appear publicly on your blog.</p>
<p><b>Post → Publish</b></p>
<DocsImage src={publishingGIF} alt="Publishing" />

<h3 id="scheduling">Scheduling</h3>
<p>You can also schedule the post at a specific date and time. Hyvor Blogs will automatically publish your post at the specified time.</p>
<p><b>Post → Publish → Publish Later → Schedule</b></p>
<DocsImage src={publishScheduleGIF} alt="Scheduling" />

<h3 id="unpublishing">Unpublishing</h3>
<p>You can unpublish a published post. The post's status will be change to Draft. Therefore, it will no longer appear on the blog. You can re-publish it later.</p>
<p><b>Post → Unpublish</b></p>
<DocsImage src={unpublishGIF} alt="Unpublishing" />

<h3 id="deleting">Deleting</h3>
<p>You can also permanently delete a post. Note that there is no way to restore a post after deleting. Consider Unpublishing if just want to hide the post from your blog.</p>
<p><b>Post → Settings → Delete Post</b></p>
<DocsImage src={deleteGIF} alt="Deleting" />

<h2 id="other-guides">Other Guides</h2>
<h3 id="auto-saving">Auto-saving & Post History</h3>
<p>If you are editing a draft, Hyvor Blogs will automatically save your post every 15 seconds if any post data (content or metadata) has been edited. You can also manually save your post by pressing <b>Ctrl + S</b>. Check the bottom right corner of the editor to see the status of the auto-saving.</p>
<DocsImage src={savingGIF} alt="Auto-saving" />
<p>If the content is edited, a post history will be created. You can refer back to this history if you want to revert to a previous version of your post. A single post can have up to 25 post histories.</p>

<h3 id="editing-published">Editing a published post</h3>
<p>You can make changes to a published post content at any time. However, the changes will not be visible to the public until you publish the changes.</p>
<DocsImage src={editorImg} alt="Editor" />
<ol>
    <li><b>Update</b> - Publish the changes to the post. The post will be updated immediately.</li>
    <li><b>Discard</b> - Discard the changes and revert to the published version of the post.</li>
    <li><b>Editing published</b> - This message will be shown if you are editing a published post.</li>
    <li>This message can be one of these:
        <ul>
            <li><b>Unsaved</b> - The changes you have made to the post are not saved yet. Press <b>Ctrl + S</b> to save the changes. Or, it will be saved automatically in 15 seconds.</li>
            <li><b>Saved</b> - All changes are saved</li>
        </ul>
    </li>
</ol>

<Callout type="info">
    <p>Note that metadata changes will be saved immediately and will be visible to the public.</p>
</Callout>

<h3 id="multi-language">Multi-language posts</h3>
<p>If you have set up multiple languages for your blog, you will see the language codes at the top of the post editor. Click on a language code to switch to that language variant of the post. Each variant should be published separately. See our <a href="/docs/languages">languages</a> guide, which explains everything you need to know about publishing multi-language posts.</p>

<h2 id="seo-analysis">SEO Analysis</h2>
<p>The SEO analysis tool in the post editor will give you suggestions to improve your post's SEO. It works based on pre-defined rules, inspired by the <a href="https://rankmath.com/kb/score-100-in-tests" rel="nofollow">Rank Math</a> WordPress plugin.</p>
<DocsImage src={SEOImg} alt="SEO Analysis" width={400} />
<Callout info="info">
<p><b>Important!</b></p>

<p>SEO analysis is <b>merely a suggestion</b>. Getting a higher score alone will not make your posts rank high. There are also other factors that affect your SEO, such as backlinks, domain authority, etc. However, these suggestions will help you better optimize your content for search engines.</p>
</Callout>

<p>To start analyzing your post, add a primary keyword for your post. You can also add secondary keywords. Hyvor Blogs will then analyze your post content and metadata and give you suggestions in real-time to improve SEO for your post.</p>
<p>These are the tests that Hyvor Blogs will run on your post:</p>
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
    <p>If the primary keyword is <code>blogging platforms</code>, we check for <code>blogging-platforms</code> in the slug. It is recommended to set a <b>short slug with hyphens</b>. In this case, the score will be:</p>
        <ul>
            <li>100% if the slug matches exactly <code>blogging-platforms</code></li>
            <li>75% if the slug contains <code>blogging-platforms</code> with other words</li>
        </ul>

    <li><b>Primary keyword in the beginning of the content</b></li>
    <p>If your content is longer than 300 words, the primary keyword should be in the first 10% of the content. If it is shorter than 300 words, it should be somewhere in the content.</p>

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
    <p> At least one external link should be present in the post.</p>

    <li><b>Internal links</b></li>
    <p>At least one internal link should be present in the post. Links to any subdomain of your main domain will be considered as internal links. See <a href="/docs/writing#link-types">link types</a> for more information. <code>internal-blog</code>, <code>internal-domain</code>, and <code>internal-root-domain</code> links are considered as internal links.</p>

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
<p>The link analysis tool in the post editor analyzes the status of the links in your post as you write. It will show you warding if there are any broken or redirect links in your post. It also shows you the <a href="/docs/writing#link-types">type of each link</a>.</p>
<DocsImage src={linkAnalysisImg} alt="Link Analysis" width={400} />
<h3 id="link-types">Link Types</h3>
<p>Hyvor Blogs categorizes links into the following types.</p>
<Table columns="2fr 3fr" hover>
    <TableRow header>
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

<p>Go to <b>Console → Tools → Link Analysis</b> to</p>
<ul>
    <li>see an overview of all links in your blog</li>
    <li>view results of bi-weekly link analysis</li>
    <li>change email report settings</li>
</ul>

<h2 id="gpt-writing">GPT Writing</h2>
<p>We have integrated GPT 3.5 directly into the editor to help you with AI content generation tasks such as</p>
<ul>
    <li>Generating a blog outline</li>
    <li>Writing a blog post</li>
    <li>Writing an article</li>
    <li>and more...</li>
</ul>

<p>You can use the default prompts in most cases. However, you can also customize the prompts to get better results.</p>
<DocsImage src={AIGIF} alt="GPT Writing" />