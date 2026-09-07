<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Writing (Basics)

Let's learn how to use the Hyvor Blogs Editor and publish your **first post!**.

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

<h2 id="gpt-writing">GPT Writing</h2>

We have integrated GPT 3.5 directly into the editor to help you with AI content generation tasks such as

- Generating a blog outline
- Writing a blog post
- Writing an article
- and more...

You can use the default prompts in most cases. However, you can also customize the prompts to get better results.

<DocsImage src="/images/docs/writing/ai.gif" alt="GPT Writing" />
