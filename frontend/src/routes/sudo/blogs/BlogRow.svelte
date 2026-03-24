<script lang="ts">
	import type { Blog, Organization } from '../types';

	interface Props {
		blog: Blog;
		org: Organization | null;
	}

	let { blog, org }: Props = $props();

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

<a class="row" href={`/sudo/blogs/${blog.id}`}>
	<div class="id">#{blog.id}</div>
	<div class="blog-info">
		<div class="blog-name">
			{blog.variants[0]?.name || 'No name'}
		</div>
		<div class="blog-description">
			{blog.variants[0]?.description || 'No description'}
		</div>
		<div class="blog-subdomain">
			{blog.subdomain}
		</div>
	</div>
	<div class="org">
		{#if org}
			{org.name}
		{:else}
			<span class="no-org">-</span>
		{/if}
	</div>
	<div class="type">
		{blog.type || '-'}
	</div>
	<div class="hosting">
		{getHostingUrl(blog)}
	</div>
</a>

<style>
	.row {
		display: grid;
		padding: 10px 25px;
		border-radius: 20px;
		cursor: pointer;
		grid-template-columns: 60px 1fr 150px 100px 1fr;
		align-items: start;
		font-size: 14px;
	}
	.row:hover {
		background-color: var(--hover);
	}
	.id {
		font-size: 12px;
		color: var(--text-light);
		font-weight: 600;
		padding-top: 2px;
	}
	.blog-name {
		font-weight: 600;
	}
	.blog-description {
		color: var(--text-light);
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.blog-subdomain {
		font-size: 12px;
		color: var(--text-light);
	}
	.org {
		font-size: 13px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.no-org {
		color: var(--text-light);
	}
	.hosting {
		word-break: break-all;
	}
</style>
