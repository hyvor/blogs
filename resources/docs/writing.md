# Writing (and Publishing)

In this page, you will learn everything about writing posts in Hyvor Blogs.

* [Using the Editor](#editor)
  * [Inline Styles](#inline-styles)
  * [Links](#links)
  * [Blocks](#blocks)
  * [Images](#images)
  * [Embeds](#embeds)
  * [Link Bookmarks](#link-bookmarks)
  * [Code Blocks](#code)
  * [Custom HTML/Twig](#custom-html)
  * [Custom Blocks](#custom-blocks)
* [Keyboard Shortcuts (Markdown-like)](#shortcuts)
* Adding Tags
* Adding Users
* Changing meta data
* Adding custom code
* Publishing, unpublishing, and deleting

## Using the Editor {#editor}

Hyvor Blogs comes with a rich text editor that supports a bunch of inline styles and blocks.

### Inline Styles {#inline-styles}

To add inline styles to text, select the text. A popup window will be opened with the available options. Click on the inline style you want to add. The following inline styles are supported.

* Bold
* Italic
* Inline Code
* Strike

<p>
<img src="/img/docs/writing-inline-styles.png" alt="Inline Styles Popup in the Console" width="350" />
</p>

### Links {#links}

Adding links is similar to adding [inline styles](#inline-styles). Select the text you want to link and then click the Link icon. Next, paste the URL in the input and hit Enter.

<p>
<img src="/img/docs/writing-link-add.gif" alt="Adding a link to a post" width="350" />
</p>

To remove a link, select the text and click on the link icon again.

<p>
<img src="/img/docs/writing-link-remove.gif" alt="Removing a link from a post" width="350" />
</p>

### Blocks {#blocks}

The term "blocks" is used to refer to block-like elements you can add to posts, such as paragraphs and blockquotes. Paragraphs are the basic block. You can create a paragraph by pressing `Enter` anywhere inside the editor.

To add other blocks, type `/` in a new line to open the blocks list. Use the mouse or up and down arrows to navigate through the list.

<p id="block-adding">
<img src="/img/docs/writing-block-add.gif" alt="Adding a block to a post" width="350" />
</p>

Hyvor Blogs supports the following blocks.

* Paragraph (Default Block)
* Heading (H1 to H6)
* Unordered List
* Ordered list
* Blockquote - Usually for quoting something
* Callout - To make something standout from other content
* [Image](#images)
* [Embed](#embeds) - To embed content from other platforms
* [Link Bookmark](#link-bookmarks)
* [Code Block](#code) - To add code examples
* Divider - To divide sections in the post
* [Custom HTML/Twig](#custom-html) - Place custom HTML/Twig inside the post
* [Custom Block](#custom-blocks)

### Images {#images}

To add images, [open the blocks list](#block-adding) (`/` in a new line), and choose **Image**. You will see a new element added to your post. It allows to you choose an image from [Unsplash](https://unsplash.com/) or upload one from your device.

For uploads, max file size is **50MB**. The following formats are supported.

* PNG - `.png`
* JPEG - `.jpg`, `.jpeg`, `.jfif`, `.pjpeg`, `.pjp`
* GIF - `.gif`
* APNG - `.apng`
* AVIF - `.avif`
* SVG - `.svg`
* WebP - `.webp`

## Formatting {#formatting}

The editor supports a number of formatting options.

### Marks {#marks}

| Name                   | Markdown              | HTML       |
|------------------------|-----------------------|------------|
| [Link](#)              | `[Anchor](/link)`     | `<a>`      |
| **Bold**               | `**text**`            | `<strong>` |
| *Italic*               | `*text*`              | `<em>`     |
| *`code`*               | <code>\`text\`</code> | `<code>`   |
| ~~strike~~             | `~~text~~`            | `<s>`      |
| Super<sup>script</sup> | `^text^`              | `<sup>`    |
| Sub<sub>script</sub>   | `~text~`              | `<sub>`    |

### Nodes {#nodes}

| Name           | Shortcut                                     | Description                                         | HTML                                                         |
|----------------|----------------------------------------------|-----------------------------------------------------|--------------------------------------------------------------|
| Paragraph      | `&#9166; Enter`                              | This is the default block.                          | `<p>`                                                        |
| Headings       | See [headings](#headings)                    | Multiple levels of headings of the post             | `<h2>` to `<h6>`                                             |
| Blockquote     | `> ` in a new line                           | A quoted text                                       | `<blockquote>`                                               |
| Callout        | `>! ` in a new line                          | A text that stands out from other content.          | `<aside>`                                                    |
| Code block     | <code>\`\`\`</code> or <code>\`\`\`js</code> | A code block                                        | `<pre><code>`                                                |
| Unordered list | `* ` in a new line                           | An unordered list. Supports nesting                 | `<ul>`                                                       |
| Ordered list   | `1. ` in a new line                          | An ordered list. Supports nesting                   | `<ol>`                                                       |
| Image          | See [images](#images)                        | An image with a caption                             | `<img>` inside `<figure>`. [See this](themes-overview#image) |
| Embed          | Paste the link in a new line and click enter | Rich embeds from third-party platforms like Youtube | [See this](themes-overview#embed-rich)                       |
| Link Bookmark  | Paste the link in a new line and click enter | Link preview like a bookmark                        | [See this](themes-overview#embed-link)                       |
