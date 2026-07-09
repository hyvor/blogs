<script lang="ts">
	import dayjs from 'dayjs';
	import type { Post } from '../../../lib/types';
	import {
		Dropdown,
		ActionList,
		ActionListItem,
		IconButton,
		toast,
		confirm
	} from '@hyvor/design/components';
	import PostStatusTag from './PostStatusTag.svelte';
	import LinkAnalysisTag from './Tags/LinkAnalysisTag.svelte';
	import SeoAnalysisTag from './Tags/SeoAnalysisTag.svelte';
	import VariantLangTag from './Tags/VariantLangTag.svelte';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconThreeDotsVertical from '@hyvor/icons/IconThreeDotsVertical';
	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';
	import AuthorTag from './AuthorTag.svelte';
	import TagChip from './TagChip.svelte';
	import { clonePost, deletePostById } from './postActions';
	import { goto } from '$app/navigation';

	interface Props {
		post: Post;
		onDelete?: (postId: number) => void;
	}

	let { post, onDelete }: Props = $props();

	let variant = $derived(post.variants[0]!);
	let showDropdown = $state(false);
	let isCloning = $state(false);
	let isDeleting = $state(false);

	const publishedAtDate = dayjs.unix(post.published_at || post.created_at).format('MMM D, YYYY');
	const createdAtDate = dayjs.unix(post.created_at).format('MMM D, YYYY');

	function handleClone(e: Event) {
		e.preventDefault();
		e.stopPropagation();

		if (isCloning) return;

		showDropdown = false;
		isCloning = true;

		const toastId = toast.loading('Cloning post...');

		clonePost(post.id)
			.then((clonedPost) => {
				toast.success('Post cloned successfully', { id: toastId });
				goto(consoleUrlWithBlog(`/posts/${clonedPost.id}`));
			})
			.catch((error) => {
				toast.error(error.message || 'Failed to clone post', { id: toastId });
			})
			.finally(() => {
				isCloning = false;
			});
	}

	async function handleDelete(e: Event) {
		e.preventDefault();
		e.stopPropagation();

		if (isDeleting) return;

		showDropdown = false;

		const confirmed = await confirm({
			title: post.is_page ? 'Delete Page' : 'Delete Post',
			content:
				`Are you sure you want to delete this ${post.is_page ? 'page' : 'post'}? ` +
				'This action is IRREVERSIBLE.',
			confirmText: 'Yes, Delete',
			danger: true
		});

		if (!confirmed) return;

		isDeleting = true;

		const toastId = toast.loading('Deleting...');

		deletePostById(post.id)
			.then(() => {
				toast.success('Deleted', { id: toastId });
				onDelete?.(post.id);
			})
			.catch((error) => {
				toast.error(error.message || 'Failed to delete', { id: toastId });
			})
			.finally(() => {
				isDeleting = false;
			});
	}
</script>

<a
	class="post-list-item"
	href={consoleUrlWithBlog(`/posts/${post.id}`)}
	style:view-transition-name={`post-${post.id}`}
>
	<div class="post-main">
		<div class="post-title-row">
			<div class="post-title">{variant?.title || '(Untitled)'}</div>
			<PostStatusTag status={variant?.status || 'draft'} size="x-small" />
		</div>

		{#if variant?.slug && variant.status === 'published'}
			<span
				class="post-slug"
				role="link"
				tabindex={0}
				onclick={(e) => {
					e.preventDefault();
					e.stopPropagation();
					window.open(post.variants[0]?.url || '', '_blank');
				}}
				onkeydown={(e) => {
					if (e.key === 'Enter') {
						e.preventDefault();
						e.stopPropagation();
						window.open(post.variants[0]?.url || '', '_blank');
					}
				}}
			>
				{post.variants[0]?.slug || ''}
				<IconBoxArrowUpRight size={10} />
			</span>
		{/if}

		<div class="post-date">
			{#if variant?.status === 'published'}
				Published {publishedAtDate}
			{:else if variant?.status === 'scheduled'}
				Scheduled {publishedAtDate}
			{:else}
				Created {createdAtDate}
			{/if}
			{#if variant?.status === 'published' && post.updated_at !== post.published_at}
				· Updated {dayjs.unix(post.updated_at).format('MMM D, YYYY')}
			{/if}
		</div>

		<div class="post-languages">
			{#each post.variants as variant (variant.id)}
				<VariantLangTag {variant} size="x-small" />
			{/each}
		</div>
	</div>

	<div class="post-authors-tags">
		{#if !post.is_page}
			<div class="post-authors">
				{#each post.authors as author (author.id)}
					<AuthorTag user={author} size="x-small" />
				{/each}
			</div>

			<div class="post-tags">
				{#each post.tags as tag (tag.id)}
					<TagChip {tag} size="x-small" />
				{/each}
			</div>
		{/if}
	</div>

	<div class="post-health-wrap">
		<div class="health-item">
			<span class="health-label">SEO</span>
			<SeoAnalysisTag postVariant={variant} />
		</div>
		<div class="health-divider"></div>
		<div class="health-item">
			<span class="health-label">Links</span>
			<LinkAnalysisTag postVariant={variant} />
		</div>
	</div>

	<div
		class="post-actions-wrap"
		role="presentation"
		onclick={(e) => {
			e.preventDefault();
			e.stopPropagation();
		}}
	>
		<Dropdown bind:show={showDropdown} align="end" width={150}>
			{#snippet trigger()}
				<IconButton
					size="small"
					color="input"
					variant="invisible"
					disabled={isCloning || isDeleting}
				>
					<IconThreeDotsVertical size={16} />
				</IconButton>
			{/snippet}

			{#snippet content()}
				<ActionList>
					<ActionListItem on:click={handleClone} disabled={isCloning || isDeleting}
						>Clone post</ActionListItem
					>
					<ActionListItem on:click={handleDelete} disabled={isCloning || isDeleting} type="danger"
						>Delete post</ActionListItem
					>
				</ActionList>
			{/snippet}
		</Dropdown>
	</div>
</a>

<style lang="scss">
	.post-list-item {
		display: grid;
		grid-template-columns: minmax(280px, 1.8fr) minmax(200px, 1.9fr) 100px 36px;
		gap: 14px;
		padding: 16px 30px;
		border-bottom: 1px solid var(--border);
		position: relative;
		cursor: pointer;
	}

	.post-list-item:hover {
		background: var(--hover);
	}

	.post-main {
		display: flex;
		flex-direction: column;
		gap: 4px;
		min-width: 0;
	}

	.post-title-row {
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.post-title {
		flex: 1;
		min-width: 0;
		font-weight: 600;
		overflow: hidden;
		text-overflow: ellipsis;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		line-clamp: 2;
		-webkit-box-orient: vertical;
	}

	.post-slug {
		display: inline-flex;
		align-items: center;
		gap: 4px;
		max-width: 100%;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		font-size: 12px;
		color: var(--link);
	}
	.post-slug:hover {
		text-decoration: underline;
	}

	.post-date {
		display: flex;
		align-items: center;
		color: var(--text-light);
		font-size: 12px;
	}

	.post-languages {
		display: flex;
		flex-wrap: wrap;
		align-content: center;
		gap: 5px;
	}

	.post-authors-tags {
		display: flex;
		flex-direction: column;
		gap: 6px;
		min-width: 0;
	}

	.post-authors,
	.post-tags {
		display: flex;
		flex-wrap: wrap;
		gap: 5px;
		min-width: 0;
	}

	.post-health-wrap {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	.health-item {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 4px;
	}

	.health-label {
		font-size: 10px;
		font-weight: 600;
		letter-spacing: 0.03em;
		text-transform: uppercase;
		color: var(--text-light);
	}

	.health-divider {
		width: 1px;
		height: 22px;
		background: var(--border);
	}

	.post-actions-wrap {
		display: flex;
		align-items: center;
		text-align: right;
		position: relative;
		z-index: 1;
	}

	.post-actions-wrap :global(.dropdown) {
		z-index: 1000;
	}

	@media (max-width: 992px) {
		.post-list-item {
			display: flex;
			flex-direction: column;
			align-items: stretch;
			gap: 10px;
		}

		.post-health-wrap {
			justify-content: flex-start;
		}

		.post-actions-wrap {
			text-align: left;
		}
	}
</style>
