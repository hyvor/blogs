<script>
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# Tags

Tags can be used to **group similar posts**. You can assign one or more tags to a post.

<h2 id="tag-index">Tag index page</h2>

Each tag creates an index page (`/tag/{slug}`), which lists the posts of that tag. This makes easy for users to browse posts of a specific tag. You can change the base URL of tag index pages (`/tag/`) by [editing routes](/docs/routes#customizing-other).

<h2 id="assign">Assigning a tag to a post</h2>

You can assign a tag to a post in the post editor. In post settings, click the + icon next to the tags field to assign a tag. Then, type the name of the tag you want to assign. If the tag exists, it will be assigned to the post. If not, you can create a new tag.

<DocsImage src="/images/docs/tags/tag-assign.png" alt="Assign a tag to a post" width={400} />

<h2 id="create">Creating a tag</h2>

There are two ways to create a tag:

1. [In the post editor](#create-in-post-editor)
2. [In tag settings](#create-in-settings)

<h3 id="create-in-post-editor">Create a tag in the post editor</h3>

You can easily create a new tag while writing a post. In post settings, click the + icon next to the tags field to [assign a tag](#assign). Then, type the name of the tag you want to create. Then click the **Create tag** button.

<DocsImage src="/images/docs/tags/tag-post-create.png" alt="Create a tag in the post editor" />

<h3 id="create-in-settings">Create a tag in tag settings</h3>

You can also create a tag in **Settings → Tags**.

<DocsImage src="/images/docs/tags/tags-create-settings.png" alt="Create a tag in tag settings" />

<h2 id="private">Private tags</h2>

Private tags are not shown publicly on your blog. They are only used for internal purposes. For example, you can use a `members-only` tag to mark posts that are only visible to members by editing your theme code, but you don't want to show this tag on the tag index page.

To make a tag private, check the **Private** checkbox in the tag settings when creating or editing a tag.

<DocsImage src="/images/docs/tags/tags-private.png" alt="Private tags" width={600} />

<h2 id="update">Updating tag settings</h2>

There is a few settings you can change for a tag in **Settings → Tags**.

- **Name**: The name of the tag. This is what users see.
- **Description**: A short description of the tag. This may be shown on the tag index page.
- **Slug**: The slug of the tag index page.
- **Custom Code**: The custom code you add here will be added to the **posts of this tag**, not to the tag index page.

<DocsImage src="/images/docs/tags/tag-edit.png" alt="Edit a tag" width={400} />

<h2 id="delete">Deleting a tag</h2>

You can delete a tag in **Settings → Tags**. This tag will be unassigned from all posts.

<DocsImage src="/images/docs/tags/tag-delete.png" alt="Delete a tag" width={400} />
