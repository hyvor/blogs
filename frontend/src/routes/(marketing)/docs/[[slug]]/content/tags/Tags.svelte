<script>
	import { DocsImage } from '@hyvor/design/marketing';

	import tagPostPng from './tag-post-create.png';
	import tagCreateSettings from './tags-create-settings.png';
	import tagEdit from './tag-edit.png';
	import tagDelete from './tag-delete.png';
	import tagAssign from './tag-assign.png';
	import tagsPrivate from './tags-private.png';
</script>

<h1>Tags</h1>

<p>
	Tags can be used to <strong>group similar posts</strong>. You can assign one or more tags to a
	post.
</p>

<h2 id="tag-index">Tag index page</h2>

<p>
	Each tag creates an index page (<code>/tag/{'{slug}'}</code>), which lists the posts of that tag.
	This makes easy for users to browse posts of a specific tag. You can change the base URL of tag
	index pages (<code>/tag/</code>) by
	<a href="/docs/routes#customizing-other">editing routes</a>.
</p>

<h2 id="assign">Assigning a tag to a post</h2>

<p>
	You can assign a tag to a post in the post editor. In post settings, click the + icon next to the
	tags field to assign a tag. Then, type the name of the tag you want to assign. If the tag exists,
	it will be assigned to the post. If not, you can create a new tag.
</p>

<DocsImage src={tagAssign} alt="Assign a tag to a post" width={400} />

<h2 id="create">Creating a tag</h2>

<p>There are two ways to create a tag:</p>

<ol>
	<li>
		<a href="#create-in-post-editor">In the post editor</a>
	</li>
	<li>
		<a href="#create-in-settings">In tag settings</a>
	</li>
</ol>

<h3 id="create-in-post-editor">Create a tag in the post editor</h3>

<p>
	You can easily create a new tag while writing a post. In post settings, click the + icon next to
	the tags field to <a href="#assign">assign a tag</a>. Then, type the name of the tag you want to
	create. Then click the <strong>Create tag</strong> button.
</p>

<DocsImage src={tagPostPng} alt="Create a tag in the post editor" />

<h3 id="create-in-settings">Create a tag in tag settings</h3>

<p>
	You can also create a tag in <strong>Settings &rarr; Tags</strong>.
</p>

<DocsImage src={tagCreateSettings} alt="Create a tag in tag settings" />

<h2 id="private">Private tags</h2>

<p>
	Private tags are not shown publicly on your blog. They are only used for internal purposes. For
	example, you can use a <code>members-only</code> tag to mark posts that are only visible to members
	by editing your theme code, but you don't want to show this tag on the tag index page.
</p>

<p>
	To make a tag private, check the <strong>Private</strong> checkbox in the tag settings when creating
	or editing a tag.
</p>

<DocsImage src={tagsPrivate} alt="Private tags" width={600} />

<h2 id="update">Updating tag settings</h2>

<p>
	There is a few settings you can change for a tag in <strong>Settings &rarr; Tags</strong>.
</p>

<ul>
	<li>
		<strong>Name</strong>: The name of the tag. This is what users see.
	</li>
	<li>
		<strong>Description</strong>: A short description of the tag. This may be shown on the tag index
		page.
	</li>
	<li>
		<strong>Slug</strong>: The slug of the tag index page.
	</li>
	<li>
		<strong>Custom Code</strong>: The custom code you add here will be added to the
		<b>posts of this tag</b>, not to the tag index page.
	</li>
</ul>

<DocsImage src={tagEdit} alt="Edit a tag" width={400} />

<h2 id="delete">Deleting a tag</h2>

<p>
	You can delete a tag in <strong>Settings &rarr; Tags</strong>. This tag will be unassigned from
	all posts.
</p>

<DocsImage src={tagDelete} alt="Delete a tag" width={400} />
