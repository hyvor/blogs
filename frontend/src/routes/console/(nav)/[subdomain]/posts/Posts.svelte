<script lang="ts">
	import AuthorFilter from './Filters/Author/AuthorFilter.svelte';
	import { Button, IconMessage, LoadButton, Loader, toast } from '@hyvor/design/components';
	import IconPlus from '@hyvor/icons/IconPlus';
	import type { PostListItem } from '../../../lib/types';
	import PostRow from './PostRow.svelte';
	import PostRowSkeleton from './PostRowSkeleton.svelte';
	import StatusFilter from './Filters/StatusFilter.svelte';
	import TagFilter from './Filters/Tag/TagFilter.svelte';
	import DateFilter from './Filters/Date/DateFilter.svelte';
	import SearchFilter from './Filters/SearchFilter.svelte';
	import { postListFiltersStore, type PostListFilters } from './postListStore';
	import { createPost, getPages, getPosts } from './postActions';
	import { goto } from '$app/navigation';
	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';
	import { getPrimaryLanguage } from '../../../lib/stores/languagesStore';
	import { getI18n } from '../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		pages?: boolean;
	}

	let { pages = false }: Props = $props();

	let isLoading = $state(true);
	let isLoadingMore = $state(false);
	let hasMore = $state(false);
	let posts: PostListItem[] = $state([]);
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
					error = i18n.t('console.posts.failedToLoadPages');
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
					if (more) toast.error(i18n.t('console.posts.failedToLoadMorePosts'));
					else error = i18n.t('console.posts.failedToLoadPosts');
				})
				.finally(() => {
					isLoading = false;
					isLoadingMore = false;
				});
		}
	}

	let isCreating = $state(false);

	function handleCreate() {
		isCreating = true;

		createPost(pages)
			.then((res) => {
				goto(consoleUrlWithBlog(`/posts/${res.id}/${getPrimaryLanguage().code}`));
			})
			.catch((e) => {
				toast.error(e.message);
				isCreating = false;
			});
	}

	postListFiltersStore.subscribe((filters) => loadPosts(false, filters));
</script>

<div id="posts" class="hds-box">
	<div class="top">
		<div class="title-wrap">
			<div class="title">
				{pages ? 'Pages' : 'Posts'}
			</div>
			<div class="">
				<Button size="small" on:click={handleCreate} disabled={isCreating}>
					{#snippet start()}
						{#if isCreating}
							<Loader size={14} invert />
						{:else}
							<IconPlus />
						{/if}
					{/snippet}
					{i18n.t('console.posts.new')}
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
			{#each { length: 6 } as _}
				<PostRowSkeleton />
			{/each}
		{:else if error}
			<IconMessage error message={error} />
		{:else if posts.length === 0}
			<IconMessage empty message={i18n.t('console.posts.noPostsFound')} />
		{:else}
			{#each posts as post (post.id)}
				<PostRow {post} onDelete={(postId) => (posts = posts.filter((p) => p.id !== postId))} />
			{/each}

			<div class="load-more-wrap">
				<LoadButton
					text={i18n.t('console.common.loadMore')}
					show={hasMore}
					loading={isLoadingMore}
					on:click={() => loadPosts(true)}
				/>
			</div>
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
		display: flex;
		border-bottom: 1px solid var(--border);
	}

	.middle {
		padding: 10px 0;
		flex: 1;
		overflow: auto;
	}

	.title-wrap {
		display: flex;
		align-items: center;
		flex: 1;
	}

	.load-more-wrap {
		padding: 16px 30px;
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
