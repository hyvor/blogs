# Writing (and Publishing)

In this page, you will learn everything about writing and publishing a post in Hyvor Blogs.

* [Using the Editor](#editor)
  * [Inline Styles](#inline-styles)
      * [Links](#links)
  * [Blocks](#blocks)
      * [Headings](#headings)
      * [Images](#images)
      * [Embeds](#embeds)
      * [Link Bookmarks](#link-bookmarks)
      * [Code Blocks](#code)
      * [Custom HTML/Twig](#custom-html)
      <!-- * [Custom Blocks](#custom-blocks) (Coming Soon) -->
* Post-related Data
  * [Authors](#authors)
  * [Tags](#tags)
  * [Metadata](#meta)
  * [Custom Code](#custom-code)
* Post Status
  * [Publishing](#publishing)
  * [Scheduling](#scheduling)
  * [Unpublishing](#unpublishing)
  * [Deleting](#deleting)
* [Editing a published post](#editing-published)
* [Multi-language posts](#multi-language)

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

## Post-Related Data

### Authors {#authors}

By default, the post creator is added as an author of a post. You can add more authors or even remove them (at least [Editor-level permissions](users#roles) is required).

**Console &rarr; Post &rarr; Settings &rarr; Authors**

### Tags {#tags}

You can assign one or more tags to a post. See [tags](tags) for more details about configuring tags on your blog.

**Console &rarr; Post &rarr; Settings &rarr; Tags**


### Metadata {#metadata}

### Custom Code {#custom-code}

You can add custom code at **Console &rarr; Post &rarr; Settings &rarr; Advanced** for a specific post. See our [custom code](custom-code) guide for more information on different ways of adding custom code.

## Post Status

### Publishing {#publishing}

Once you have finished writing your post, you can publish it. Once published, the post will appear publicly on your blog.


**Console &rarr; Post &rarr; Publish**

<p>
<img src="/img/docs/writing-publishing.gif" alt="Publishing a post in Hyvor Blogs" width="350" />
</p>

### Scheduling {#scheduling}

You can also schedule the post at a specific date and time. Hyvor Blogs will automatically publish your post at the specified time.

**Console &rarr; Post &rarr; Publish &rarr; Publish Later &rarr; Schedule**

<p>
<img src="/img/docs/writing-scheduling.gif" alt="Publishing a post in Hyvor Blogs" width="350" />
</p>

### Unpublishing {#unpublishing}

You can unpublish a published post. The post's status will be change to **Draft**. Therefore, it will no longer appear on the blog. You can re-publish it later.

**Console &rarr; Post &rarr; Unpublish**

<p>
<img src="/img/docs/writing-unpublishing.gif" alt="Publishing a post in Hyvor Blogs" width="550" />
</p>

### Deleting {#deleting}

You can also permanently delete a post. Note that there is no way to restore a post after deleting. Consider Unpublishing if just want to hide the post from your blog.

**Console &rarr; Post &rarr; Settings  &rarr; Delete Post**

<p>
<img src="/img/docs/writing-deleting.gif" alt="Publishing a post in Hyvor Blogs" width="550" />
</p>

## Editing a published post {#editing-published}



## Multi-Language Posts {#multi-language}

Hyvor Blogs support multi-language posts. See [languages](languages) guide for more details.