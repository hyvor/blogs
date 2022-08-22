# Writing (and Publishing)

This page explains everything about publishing a post on Hyvor Blogs.

* [Posts & Pages](#posts-pages)
* [Editor](#editor)
  * [Inline Styles](#inline-styles)
  * [Blocks](#blocks)
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

## Posts & Pages {#posts-pages}

Posts: 
* are the main part of your blog, where you share your ideas, thoughts, and stories. 
* appear in the index page or other collection pages
* can be created, edited, and deleted at **Console &rarr; Posts**
* have authors and tags

Pages:

* ex: contact us page, about us page
* contain static information
* do not appear in the index page.
* usually is linked to in header or footer [navigations](navigation).
* can create, edit, and delete pages at **Console &rarr; Pages**
* do not have authors or tags

Both:
* are uniquely identified by the slug
* by default have `/{slug}` permalink. See [this](routes#permalinks) if you want to change post/page permalinks, for example to include year and month in the URL.

## Editor {#editor}

Hyvor Blogs comes with a rich text editor that supports inline styles and blocks.

### Inline Styles {#inline-styles}

To add inline styles to text, select the text. A popup window will be opened with the available options. Click on the inline style you want to add. The following inline styles are supported.

* Bold
* Italic
* Inline Code
* Strike

<p>
<img src="/img/docs/writing-inline-styles.png" alt="Inline Styles Popup in the Console" width="350" />
</p>

#### Links {#links}

Adding links is similar to adding [inline styles](#inline-styles). Select the text you want to link and then click the Link icon. Next, paste the URL in the input and hit Enter.

<p>
<img src="/img/docs/writing-link-add.gif" alt="Adding a link to a post" width="350" />
</p>

To remove a link, select the text and click on the link icon again.

<p>
<img src="/img/docs/writing-link-remove.gif" alt="Removing a link from a post" width="350" />
</p>

#### Markdown for Inline Styles {#markdown-inline-styles}

You can also use Markdown shortcuts to create inline styles.

| Inline Style           | Markdown Shortcut               |
|------------------------|---------------------------------|
| [Link](#links)             | `[Anchor](https://example.com)` |
| **Bold**               | `**text**`                      |
| *Italic*               | `*text*`                        |
| *`code`*               | <code>\`text\`</code>           |
| ~~strike~~             | `~~text~~`                      |
| Super<sup>script</sup> | `^text^`                        |
| Sub<sub>script</sub>   | `~text~`                        |

### Blocks {#blocks}

The term "blocks" is used to refer to block-like elements you can add to posts, such as paragraphs and blockquotes. Paragraphs are the basic blocks. You can create a paragraph by pressing `Enter` anywhere inside the editor.

To add other blocks, use the slash command: type slash (`/`) in a new line to open the blocks list. Use the mouse or up/down arrows to navigate through the list.

<p id="slash-command">
<img src="/img/docs/writing-block-add.gif" alt="Adding a block to a post using slash command" width="350" />
</p>

Hyvor Blogs supports the following blocks.

* Paragraph
* Divider
* [Heading](#headings)
* [Lists](#lists)
* [Quote](#quote)
* [Callout](#callout)
* [Image](#image)
* [Embed](#embed)
* [Link Bookmark](#link-bookmark)
* [Code Block](#code-block)
* [Custom HTML/Twig](#custom-html)
<!-- * [Custom Block](#custom-blocks) -->

#### Headings {#headings}

HB supports headings from `<h1>` to `<h6>`. The slash command only provides two options: Large (h2) and Medium (h3). Other headings can be added using Markdown syntax in a new line.

* `#` + `space` for `h1`
* `##` + `space` for `h2`
* `###` + `space` for `h3`
* ...

<p>
<img src="/img/docs/writing-headings.gif" alt="Adding Headings to Posts in Hyvor Blogs" width="350" />
</p>

> Please note that the reason to give **Large Heading** h2 is that h1 is reserved for the post title. However, you may use h1 within your posts if needed.

##### Heading IDs {#heading-ids}

There are two ways to add heading IDs.

1. Focus the ID input at the top of the heading and type the ID there.

<p>
<img src="/img/docs/writing-heading-id-input.png" alt="Adding an ID to headings in Posts in Hyvor Blogs" width="350" />
</p>

2. Type Markdown-style heading ID (`{#heading-id}`) at the end of the heading.

<p>
<img src="/img/docs/writing-heading-id-markdown.gif" alt="Adding an ID to headings in Posts in Hyvor Blogs using Markdown Syntax" width="450" />
</p>

#### Lists {#lists}

| Action                | Command |
|-----------------------| --- |
| Create unordered list | `* + space` or `- + space`
| Create ordered list   | `1. + space`, `2. + space`, etc.
| New list item | `Enter`
| Increase indent       | `Tab`
| Decrease indent | `Shift + Tab`

<p>
<img src="/img/docs/writing-lists.gif" alt="Creating a list in Hyvor Blogs Editor" width="450" />
</p>

#### Quote {#quote}

Quote block is usually used to quote something, while it is also generally used make text stand out. You can create a Quote block via the [slash command](#slash-command) or by entering `> + space` in a new line (Markdown syntax).

<p>
<img src="/img/docs/writing-quotes.gif" alt="Creating a Quote Block in Hyvor Blogs Editor" width="450" />
</p>

You can get out of the Quote block by clicking `Enter` in a new line within the Quote block.

#### Callout {#callout}

Callout block is used to write text that stands out from other content in the post. You can set an emoji and background/font colors for each callout block.

<p>
<img src="/img/docs/writing-callout.gif" alt="Creating a Callout Block in Hyvor Blogs Editor" width="450" />
</p>

#### Image {#image}

To add images, use [slash command](#slash-command) (`/` in a new line), and choose **Image**. You will see a new element added to your post. It allows to you choose an image from [Unsplash](https://unsplash.com/) or upload one from your device.

For uploads, max file size is **50MB**. The following formats are supported.

* PNG - `.png`
* JPEG - `.jpg`, `.jpeg`, `.jfif`, `.pjpeg`, `.pjp`
* GIF - `.gif`
* APNG - `.apng`
* AVIF - `.avif`
* SVG - `.svg`
* WebP - `.webp`

> Images added via Unsplash are hosted at unsplash.com and not uploaded to [media](media) of your blog.

#### Embed {#embed}

Hyvor Blogs supports embedding content from 1900+ online platforms. We use [iframely](https://iframely.com/) under the hood to support various platforms.

An embed can be added via the [slash command](#slash-command) (**`/` in a new line &rarr; Embed**). Then, paste the URL you would like to embed in the input box.

<p>
<img src="/img/docs/writing-embed.gif" alt="Embedding Content from Other Platforms in Hyvor Blogs" width="450" />
</p>

#### Link Bookmark {#link-bookmark}



#### Code Block {#code-block}

#### Custom HTML/Twig {#custom-html}

## Post-Related Data

### Authors {#authors}

By default, the post creator is added as an author of a post. You can add or remove authors (at least [Editor-level permissions](users#roles) is required).

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