<script lang="ts">
	import { Tag, toast } from '@hyvor/design/components';
	import type { Blog } from '../types';

	function copyId(e: any) {
		e.preventDefault();
		e.stopPropagation();
		navigator.clipboard.writeText(blog.id.toString());
		toast.success('ID copied to clipboard');
	}

	interface Props {
		blog: Blog;
	}

	let { blog }: Props = $props();

	function getHostingUrl(blog: Blog): string {
		if (blog.hosting_at === 'domain' && blog.hosting_domain) {
			return blog.hosting_domain;
		}
		if (blog.hosting_at === 'self' && blog.hosting_url) {
			return blog.hosting_url;
		}
		return blog.subdomain + '.blogs.hyvor.com';
	}
</script>

<a class="blog" href={`/sudo/blogs/${blog.id}`}>
	<div class="id">
		<Tag interactive on:click={copyId}>
			ID: {blog.id}
		</Tag>
	</div>

	<div class="blog-name">
		{blog.variants[0]?.name || 'No name'}
	</div>

	<div class="blog-name">
		{blog.variants[0]?.description || 'No description'}
	</div>

	<div class="blog-info">
		Type: {blog.type || '-'}
	</div>

	<div class="hosting">
		<span>URL</span>
		{getHostingUrl(blog)}
	</div>
</a>

<style>
	.blog {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 5px 15px;
		border-radius: 20px;
		font-size: 14px;
		flex: 1;
	}
	.blog:hover {
		background-color: var(--hover);
	}
	.id {
		width: 100px;
	}
	.hosting {
		flex: 1;
		font-size: 14px;
	}
	.hosting span {
		display: block;
		font-weight: bold;
		margin-bottom: 4px;
	}
	.blog-name {
		width: 200px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.blog-info {
		width: 100px;
	}
</style>
