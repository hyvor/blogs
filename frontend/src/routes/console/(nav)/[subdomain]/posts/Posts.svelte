<script lang="ts">
	import AuthorFilter from './Filters/Author/AuthorFilter.svelte';
	import { Button, IconMessage, LoadButton, Loader, toast } from '@hyvor/design/components';
	import IconPlus from '@hyvor/icons/IconPlus';
	import type { Post } from '../../../lib/types';
	import PostRow from './PostRow.svelte';
	import StatusFilter from './Filters/StatusFilter.svelte';
	import TagFilter from './Filters/Tag/TagFilter.svelte';
	import DateFilter from './Filters/Date/DateFilter.svelte';
	import SearchFilter from './Filters/SearchFilter.svelte';
	import { postListFiltersStore, type PostListFilters } from './postListStore';
	import { createPost, getPages, getPosts } from './postActions';
	import { goto } from '$app/navigation';
	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';

	interface Props {
		pages?: boolean;
	}

	let { pages = false }: Props = $props();

	let isLoading = $state(true);
	let isLoadingMore = $state(false);
	let hasMore = $state(false);
	let posts: Post[] = $state([]);
	let error: null | string = $state(null);

	function getTime(date: Date | null) {
		if (!date) return undefined;
		return Math.floor(date.getTime() / 1000);
	}

	const limit = 50;

	function loadPosts(more = false, filters: PostListFilters | null = null) {
		filters = filters || $postListFiltersStore;

		more ? (isLoadingMore = true) : (isLoading = true);
		if (!more) posts = [];
		error = null;

		if (pages) {
			getPages()
				.then((res) => {
					posts = res;
				})
				.catch(() => {
					error = 'Failed to load pages';
				})
				.finally(() => {
					isLoading = false;
				});
		} else {
			getPosts({
				status: filters.status || undefined,
				author_id: filters.author?.id || undefined,
				tag_id: filters.tag?.id || undefined,
				start_timestamp: getTime(filters.startDate),
				end_timestamp: getTime(filters.endDate),
				search: filters.search || undefined,
				limit,
				offset: more ? posts.length : 0
			})
				.then((res) => {
					posts = more ? [...posts, ...res] : res;
					hasMore = res.length === limit;
				})
				.catch(() => {
					if (more) toast.error('Failed to load more posts');
					else error = 'Failed to load posts';
				})
				.finally(() => {
					isLoading = false;
					isLoadingMore = false;
				});
		}
	}

	let isCreating = $state(false);

	function handleCreate() {
		const toastId = toast.loading(`Creating ${pages ? 'page' : 'post'}...`);
		isCreating = true;

		createPost(pages)
			.then((res) => {
				toast.success(`${pages ? 'Page' : 'Post'} created`, { id: toastId });
				goto(consoleUrlWithBlog(`/posts/${res.id}`));
			})
			.catch((e) => {
				toast.error(e.message, { id: toastId });
			})
			.finally(() => {
				isCreating = false;
			});
	}

	postListFiltersStore.subscribe((filters) => loadPosts(false, filters));
</script>

<div id="posts">
	<div class="top">
		<div class="title-wrap">
			<div class="title">
				{pages ? 'Pages' : 'Posts'}
			</div>
			<div class="">
				<Button size="small" on:click={handleCreate} disabled={isCreating}>
					{#snippet start()}
						<IconPlus />
					{/snippet}
					New
				</Button>
			</div>
		</div>

		{#if !pages}
			<div class="filters">
				<StatusFilter />
				<AuthorFilter />
				<TagFilter />
				<DateFilter />
				<SearchFilter />
			</div>
		{/if}
	</div>

	<div class="middle">
		{#if isLoading}
			<div class="loader-wrap">
				<Loader size="large" block padding={100} />
			</div>
		{:else if error}
			<IconMessage error message={error} />
		{:else if posts.length === 0}
			<IconMessage empty message="No posts found" />
		{:else}
			{#each posts as post (post.id)}
				<PostRow {post} />
			{/each}

			<LoadButton
				text="Load more"
				show={hasMore}
				loading={isLoadingMore}
				on:click={() => loadPosts(true)}
			/>
		{/if}
	</div>
</div>

<style lang="scss">
	#posts {
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.top {
		padding: 20px 25px;
		background-color: var(--box-background);
		border-radius: var(--box-radius);
		box-shadow: var(--box-shadow);
		display: flex;
	}

	.middle {
		padding: 20px 25px;
		background-color: var(--box-background);
		border-radius: var(--box-radius);
		box-shadow: var(--box-shadow);
		margin-top: 15px;
		flex: 1;
		overflow: auto;
	}

	.title-wrap {
		display: flex;
		align-items: center;
		flex: 1;
	}

	.loader-wrap {
		display: flex;
		align-items: center;
		justify-content: center;
		height: 100%;
	}

	.title {
		font-size: 1.2rem;
		font-weight: 600;
		margin-right: 10px;
	}

	.filters {
		display: flex;
		gap: 7px;
	}

	@media (max-width: 992px) {
		.top {
			flex-direction: column;
			gap: 15px;
		}
		.filters {
			flex-wrap: wrap;
			:global(.dropdown) {
				display: block !important;
				width: calc(50% - 7px);
			}
			:global(.dropdown .trigger > button),
			:global(.dropdown .content-wrap),
			:global(.dropdown .content-wrap .content) {
				width: 100% !important;
			}
			:global(.input-wrap) {
				width: 100%;
			}
		}
	}
</style>
