# Writing (and Publishing)

This page explains everything about publishing a post on Hyvor Blogs.

* [Posts & Pages](#posts-pages)
* [Editor](#editor)
  * [Inline Styles](#inline-styles)
  * [Blocks](#blocks)
* [Post Metadata](#metadata) (Authors, Tags, Custom Code)
* [Post Status](#status)
  * [Publishing](#publishing)
  * [Scheduling](#scheduling)
  * [Unpublishing](#unpublishing)
  * [Deleting](#deleting)
* [Other Guides](#other-guides)
  * [Auto-saving & Post History](#auto-saving)
  * [Editing a published post](#editing-published)
  * [Multi-language posts](#multi-language)
* [SEO Analysis](#seo-analysis)
* [Link Analysis](#link-analysis)
* [GPT Writing](#gpt-writing)

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

To add inline styles to text, select the text. A popup will be shown with the available options. Click on the inline style you want to add. The following inline styles are supported.

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

> Please note that the reason to give **Large Heading** uses `<h2>` is that `<h1>` is reserved for the post title in your theme. However, you may use h1 within your posts if needed.

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

To add images, use [slash command](#slash-command) (`/` in a new line), and choose **Image**. You can add an image in one of the following ways:

- Upload from your device
- Upload from a URL
- Choose from [Unsplash](https://unsplash.com/)

![Adding an image to a post in Hyvor Blogs](/img/docs/writing-image-upload.gif)

For uploads, max file size is **50MB**. The following formats are supported.

* PNG - `.png`
* JPEG - `.jpg`, `.jpeg`, `.jfif`, `.pjpeg`, `.pjp`
* GIF - `.gif`
* APNG - `.apng`
* AVIF - `.avif`
* SVG - `.svg`
* WebP - `.webp`

> Image uploaded from device or a URL are automatically added to your [media](media) ensuring their availability without depending on an external service. However, images added via Unsplash are hosted at unsplash.com and not uploaded to blog media.

The image node also allows you to:

- Add a caption
- Add ALT text
- Scale the image

#### Embed {#embed}

Hyvor Blogs supports embedding content from 1900+ online platforms. We use [iframely](https://iframely.com/) under the hood to support various platforms. You can add an embed using the [slash command](#slash-command) (**`/` in a new line &rarr; Embed**). Then, paste the URL you would like to embed in the input box.

<p>
<img src="/img/docs/writing-embed.gif" alt="Embedding Content from Other Platforms in Hyvor Blogs" width="450" />
</p>

#### Link Bookmark {#link-bookmark}

You can use Link Bookmark block to add a rich link previews to your posts. To insert a link bookmark, type **`/` in a new line &rarr; Link Bookmark**. Then, paste the URL you would like to embed in the input box and press Enter. Hyvor Blogs will generate a rich preview of the link using OG tags and other metadata.

![Adding a Link Bookmark to a post in Hyvor Blogs](/img/docs/writing-bookmark.gif)

> The URL you enter should be publicly accessible to generate a preview. For example, you cannot generate link bookmarks for private social media posts.

#### Code Block {#code-block}

You can add code blocks in two ways:

- **`/` in a new line &rarr; Code Block**
- Type <code>\`\`\`lang</code> or <code>\`\`\`js</code> (with language code) in a new line and press `Enter`.

![Adding a Code Block to a post in Hyvor Blogs](/img/docs/writing-code-block.png)

See [Syntax Highlighting](syntax-highlighting) to learn more about supported languages, supported themes, and using annotations.

#### Custom HTML/Twig {#custom-html}

The Custom HTML/Twig block allows you to add custom HTML or Twig code to your posts. You can use this to add custom elements to your posts. For example, you can add a custom form, a custom widget, etc. You can use Twig [variables](themes-templates#variables) in the code.

## Post Metadata {#metadata}

Click the Settings button in the editor to open the post settings.

<p>
<img src="/img/docs/writing-metadata.png" alt="Post Settings (Metadata) in Hyvor Blogs" width="450" />
</p>

You can configure the following settings for a post:

* **Slug** - A unique URL-friendly identifier for the post. If you don't set a slug, Hyvor Blogs will automatically generate one based on the post title when publishing the post.
* **Publish Time** - The publish time will be added automatically when publishing the post. However, you can set a custom publish time for a post.
* **Authors** - You can add [users](users) in your blog as authors of a post. By default, the post creator is added as an author of a post. Users with [editor-level permissions](users#roles) can add or remove authors of a post. One post can have multiple authors.
* **Tags** - You can assign one or more [tags](tags) to a post.
* **Description** - A short description of the post. This will be used as the meta description of the post for search engines and social media.
* **Featured Image** - The featured image of the post. This will be used as the meta image of the post for search engines and social media.

In advanced settings, you can add the following:

<p>
<img src="/img/docs/writing-metadata-advanced.png" alt="Post Advanced Settings (Metadata) in Hyvor Blogs" width="450" />
</p>

* **Canonical URL** - If you have published the same post on a different location, you can add the canonical URL of the post here.
* **Code Head** - Custom code to add right before the `</head>` tag of the post.
* **Code Foot** - Custom code to add right before the `</body>` tag of the post.

> See [custom code](custom-code) documentation for more information on different ways of adding custom code to your blog.

## Post Status {#status}

A post can have one of the following statuses:

- **Draft** - Not visible to the public. Only [users](users) of your blog can see the post in the Console.
- **Scheduled** - Not visible to the public. It will be published automatically at the specified time.
- **Published** - Visible to the public.

### Publishing {#publishing}

Once you have finished writing your post, you can publish it. Once published, the post will appear publicly on your blog.


**Post &rarr; Publish**

<p>
<img src="/img/docs/writing-publishing.gif" alt="Publishing a post in Hyvor Blogs" width="550" />
</p>

### Scheduling {#scheduling}

You can also schedule the post at a specific date and time. Hyvor Blogs will automatically publish your post at the specified time.

**Post &rarr; Publish &rarr; Publish Later &rarr; Schedule**

<p>
<img src="/img/docs/writing-scheduling.gif" alt="Publishing a post in Hyvor Blogs" width="550" />
</p>

### Unpublishing {#unpublishing}

You can unpublish a published post. The post's status will be change to **Draft**. Therefore, it will no longer appear on the blog. You can re-publish it later.

**Post &rarr; Unpublish**

<p>
<img src="/img/docs/writing-unpublishing.gif" alt="Publishing a post in Hyvor Blogs" width="550" />
</p>

### Deleting {#deleting}

You can also permanently delete a post. Note that there is no way to restore a post after deleting. Consider Unpublishing if just want to hide the post from your blog.

**Post &rarr; Settings  &rarr; Delete Post**

<p>
<img src="/img/docs/writing-deleting.gif" alt="Publishing a post in Hyvor Blogs" width="600" />
</p>

## Other Guides {#other-guides}

### Auto-saving and Post History {#auto-saving}

If you are editing a draft, Hyvor Blogs will automatically save your post every 15 seconds if any post data (content or metadata) has been edited. You can also manually save your post by pressing **Ctrl + S**. Check the bottom right corner of the editor to see the status of the auto-saving.

![Writing Auto-Saving](/img/docs/writing-autosaving.gif)

If the content is edited, a post history will be created. You can refer back to this history if you want to revert to a previous version of your post. A single post can have up to 25 post histories.

### Editing a published post {#editing-published}

You can make changes to a published post content at any time. However, the changes will not be visible to the public until you publish the changes.

![Editing a published post in Hyvor Blogs](/img/docs/writing-editing-published.png)

1. **Publish Changes** - Publish the changes to the post. The post will be updated immediately.
2. **Discard Changes** - Discard the changes and revert to the published version of the post.
3. **You are editing a published post** - This message will be shown if you are editing a published post.
4. This message can be one of these:
   - **Unsaved Changes\*** - The changes you have made to the post are not saved yet. Press **Ctrl + S** to save the changes. Or, it will be saved automatically in 15 seconds.
   - **Saved** - All changes are saved 

> Note that metadata changes will be saved immediately and will be visible to the public.

### Multi-Language Posts {#multi-language}

If you have set up multiple languages for your blog, you will see the language codes at the top of the post editor. Click on a language code to switch to that language variant of the post. Each variant should be published separately. See our [languages](languages) guide, which explains everything you need to know about publishing multi-language posts.

## SEO Analysis {#seo-analysis}

The SEO analysis tool in the post editor will give you suggestions to improve your post's SEO. It works based on pre-defined rules, inspired by the [Rank Math](https://rankmath.com/kb/score-100-in-tests) WordPress plugin.

<p>
  <img src="/img/landing/pricing/seo-feature.png" alt="SEO Analysis in Hyvor Blogs" width="350" />
</p>

> **Important!**
> 
> SEO analysis is **merely a suggestion**. Getting a higher score alone will not make your posts rank high. There are also other factors that affect your SEO, such as backlinks, domain authority, etc. However, these suggestions will help you better optimize your content for search engines.

To start analyzing your post, add a primary keyword for your post. You can also add secondary keywords. Hyvor Blogs will then analyze your post content and metadata and give you suggestions in real-time to improve SEO for your post.

These are the tests that Hyvor Blogs will run on your post:

* **Primary keyword in the title**
  * 100% if the primary keyword is at the beginning of the title
  * 75% if the primary keyword is in the first 50 characters of the title
  * 49% if the primary keyword is after the first 50 characters of the title
  * 0% if the primary keyword is not in the title
 
* **Primary keyword in the description**
* **Primary keyword in the slug**
  
  If the primary keyword is `blogging platforms`, we check for `blogging-platforms` in the slug. It is recommended to set a **short slug with hyphens**. In this case, the score will be:
  * 100% if the slug matches exactly `blogging-platforms`
  * 75% if the slug contains `blogging-platforms` with other words
* **Primary keyword in the beginning of the content**
  
  If your content is longer than 300 words, the primary keyword should be in the first 10% of the content. If it is shorter than 300 words, it should be somewhere in the content.
* **Content length**

  (The best content length depends on the topic, which is not considered here)
  * 0% for less than 400 words
  * 1% for each 25 words after 400 words (2500+ words = 100%)
* **All keywords in the content**
  
  All keywords should be present in the post content.
* **All keywords in subheadings**
  
  Each keyword should be present in at least one subheading (h2, h3, h4, h5, h6).
* **All keywords in image alt attributes**
  
  Each keyword should be present in at least one image alt attribute.
* **Keyword density**
  
  Checks for keyword density in content (`keywords count / total words`).

  * 0% for less than 0.1%
  * 50% for 0.1% to 0.5%
  * 100% for 0.5% to 2.5%
  * 50% for 2.5% to 5%
  * 0% for more than 5%
* **Slug length**

  Shorter slugs are better. This test will pass if the slug is less than 50 characters.
* **External links**
  
  At least one external link should be present in the post.
* **Internal links**

  At least one internal link should be present in the post. Links to any subdomain of your main domain will be considered as internal links. See [link types](#link-types) for more information. `internal-blog`, `internal-domain`, and `internal-root-domain` links are considered as internal links.
* **Images**
  * 70% - 1 image
  * 80% - 2 images
  * 90% - 3 images
  * 100% - 4 or more images

* **All images have alt attributes**
  
  All images should have alt attributes.


## Link Analysis {#link-analysis}

The link analysis tool in the post editor analyzes the status of the links in your post as you write. It will show you warding if there are any broken or redirect links in your post. It also shows you the [type of each link](#link-types).

<p>
  <img src="/img/landing/pricing/links-feature.png" alt="SEO Analysis in Hyvor Blogs" width="350" />
</p>


### Link Types {#link-types}

Hyvor Blogs categorizes links into the following types.

| Link Type | Description |
| --- | --- |
| `internal-blog` | Links to other posts/pages in your blog |
| `internal-domain` | Links to the same domain as your blog, but not to your blog. |
| `internal-root-domain` | Links to any domain of the root domain, but not to your blog's domain. |
| `external` | Links to other domains |
| `mail` | Mailto links (starts with `mailto:`) |
| `tel` | Tel links (starts with `tel:`) |
| `anchor` | Links to anchors in the same page (starts with `#`) |
| `other` | Other links (`ftp:`, `data:`, javascript, etc.) |

Go to **Console &rarr; Tools &rarr; Link Analysis** to

* see an overview of all links in your blog
* view results of bi-weekly link analysis
* change email report settings

## GPT Writing {#gpt-writing}

We have integrated GPT 3.5 directly into the editor to help you with AI content generation tasks such as 

* Generating a blog outline
* Writing a blog post
* Writing an article
* and more...

You can use the default prompts in most cases. However, you can also customize the prompts to get better results.

<p>
  <img src="/img/landing/homepage/console-gpt.gif" alt="GPT in the Console" />
</p>