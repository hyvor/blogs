<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import type { Blog, Organization } from '../types';
	import { flagByCountryCode } from '$lib/helpers/countryCode';

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
			<div class="org-name">
				{org.name}

				{#if org.billing_address?.country}
					<span title={org.billing_address?.country}>
						{flagByCountryCode(org.billing_address?.country)}
					</span>
				{/if}
			</div>
			<div class="org-email">
				{org.billing_email}
			</div>
			<div class="view-button">
				<Button as="a" href="/sudo/core/organizations/{org.id}" size="x-small" color="input">
					Org &rarr;
				</Button>
			</div>
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
		grid-template-columns: 60px 1fr 200px 100px 1fr;
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
	}
	.org-name {
		font-weight: 600;
	}
	.org-email {
		font-size: 14px;
	}
	.view-button {
		margin-top: 3px;
	}
	.no-org {
		color: var(--text-light);
	}
	.hosting {
		word-break: break-all;
	}
</style>
