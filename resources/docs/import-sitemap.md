# Import from Sitemap

Hyvor Blogs can import an entire blog from a sitemap. This feature assumes that all blog posts have a similar structure and blog post data can be extracted using CSS selectors and meta tags.

How it works:

* Submit your sitemap (`.xml` or `.txt`)
* Provide CSS selectors to help us extract data from your HTML pages
* Test your CSS against a couple of your pages to make sure data is correctly parsed
* Finally, import from the sitemap

Our importer is capable of extracting the following data from your HTML pages:

| Data | Extraction Method                                          |
| --- |------------------------------------------------------------|
| Post title | CSS selector or `<title>`                                  | 
| Post description | CSS selector or `<meta name="description">`                | 
| Post content | CSS selector. See [content importing](#content)            |  
| Post published date | CSS selector or `<meta property="article:published_time">` |
| Post featured image | `<meta property="og:image">`                               |
| Post slug | from URL                                                   |

It **does not** support importing following data:

* Tags - No tags will be added
* Authors - The owner will be added as an author for each blog post

### CSS Selectors {#css}

Each blog has a different structure. Therefore, we need to know how to extract data from your HTML pages. You can provide CSS selectors for each data type.

For this blog:

![CSS Selectors Example](/img/docs/import-css-selectors.png)

You can set the following CSS selectors:

* Post title: `h1`
* Post content: `section.post-content`
* Post published date: `time.publish-date`

Note that only the post content selector is required. Other selectors are optional. If you don't provide a selector for a data type, we will try to extract it from the HTML page using meta tags as explained in the above table.

### Content {#content}

Check our [writing guide](writing) to see supported styles and blocks. The importer will automatically detect most styles (bold, italic) and blocks (paragraphs, blockquotes) from generic HTML tags. 

It currently does not support importing the following block types:

* Link Bookmark
* Custom HTML/Twig

It has limited support for the following block types:

* Embed - We will try to import iframes as embeds (ex: Youtube embed). However, we cannot guarantee that it will work for all embeds.

#### Content Excluding {#content-excluding}

You can exclude some parts of your content using CSS selectors. For example, if you have ads in your blog posts, you can exclude them by adding a CSS selector to **Post Content Exclude**:

```css
.post-content > .ad
```

To exclude multiple elements, separate them using a comma:

```css
.ad, .newsletter-signup
```

### Importing Images {#images}

If you are completley migrating to Hyvor Blogs, it is possible that images will no longer will be available in the original server. Therefore, we recommend you to import images to Hyvor Blogs. To do this, make sure to keep the **Import Images** on. Then, we will import featured images and all images in the post content into your local [blog media](media). The image should be less than 50MB to be imported.

### Test & Import {#testing}

Before importing, add one URL from your blog to test the importer. We will extract data from that URL and show you the results. If the results are not correct, you can change the CSS selectors and test again.

![Import Test Results](/img/docs/impor-test-results.png)

When you are happy with the results, you can import the entire blog from your sitemap. It should take a few minutes to import. If you have any issues, please [contact us](support).