<script lang="ts">
	import { onMount } from 'svelte';
	import { SplitControl, Loader, CodeBlock, toast, IconButton } from '@hyvor/design/components';
	import IconCopy from '@hyvor/icons/IconCopy';
	import { page } from '$app/stores';
	import dayjs from 'dayjs';
	import type { Blog } from '../../types';
	import sudoApi from '../../lib/sudoApi';
	import { getHostingUrl } from '../../lib/blogUrl';

	let blog: Blog | undefined = $state();

	let id = $derived($page.params.id as string);

	function loadBlog() {
		sudoApi
			.get<Blog>({
				endpoint: `/blogs/${id}`
			})
			.then((res) => {
				blog = res;
			})
			.catch((err) => {
				toast.error(err.message);
			});
	}

	function copyId() {
		navigator.clipboard.writeText(blog!.id.toString());
		toast.success('ID copied to clipboard');
	}

	function formatTimestamp(ts: number | null): string {
		if (!ts) return '-';
		return dayjs.unix(ts).format('YYYY-MM-DD HH:mm:ss');
	}

	onMount(loadBlog);
</script>

<div class="wrap">
	{#if blog}
		<SplitControl label="ID">
			{blog.id}
			<IconButton on:click={copyId} size="small" color="input">
				<IconCopy size={12} />
			</IconButton>
		</SplitControl>
		<SplitControl label="Subdomain">
			{blog.subdomain}
		</SplitControl>
		<SplitControl label="Name">
			{blog.variants[0]?.name || ''}
		</SplitControl>
		<SplitControl label="Description">
			{blog.variants[0]?.description || ''}
		</SplitControl>
		<SplitControl label="Created At">
			{formatTimestamp(blog.created_at)}
		</SplitControl>
		<SplitControl label="Type">
			{blog.type || '-'}
		</SplitControl>
		<SplitControl label="Hosting">
			{@const hostingUrl = getHostingUrl(blog)}
			<a href={hostingUrl} class="hds-link" target="_blank">
				{hostingUrl}
			</a>
			<span style="color: var(--text-light);margin-left:5px;">
				({blog.hosting_at})
			</span>
		</SplitControl>
		<SplitControl label="User ID">
			{blog.hyvor_user_id ?? '-'}
		</SplitControl>
		<SplitControl label="Organization ID">
			{blog.organization_id ?? '-'}
		</SplitControl>
		<SplitControl label="Blocked">
			{#if blog.is_blocked}
				Yes — at {formatTimestamp(blog.blocked_at)}
			{:else}
				No
			{/if}
		</SplitControl>

		<SplitControl column label="Blog Object">
			<div class="json-wrap">
				<CodeBlock code={JSON.stringify(blog, null, 2)} language="json" />
			</div>
		</SplitControl>
	{:else}
		<Loader full />
	{/if}
</div>

<style>
	.wrap {
		padding: 30px;
		overflow: auto;
	}
	.json-wrap {
		max-height: 1000px;
		max-width: 70vw;
		overflow: auto;
	}
</style>
