<script lang="ts">
	import {
		Button,
		IconMessage,
		LoadButton,
		Loader,
		TableRow,
		toast
	} from '@hyvor/design/components';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import SettingsTable from '../@components/SettingsTable.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import type { Tag, TagVariant } from '../../../../lib/types';
	import { onMount } from 'svelte';
	import { getTags } from './tagActions';
	import TagRow from './TagRow.svelte';
	import CreateTagModal from './CreateTagModal.svelte';
	import { getI18n } from '../../../../lib/i18n';
	import { cant, redirectIfCant } from '../../../../lib/scope.svelte';

	const i18n = getI18n();

	let isCreating = $state(false);

	let tags: Tag[] = $state([]);
	let isLoading = $state(true);
	let isLoadingMore = $state(true);
	let hasMore = $state(false);

	const limit = 40;

	function loadTags(more = false) {
		more ? (isLoadingMore = true) : (isLoading = true);

		getTags({
			limit,
			offset: more ? tags.length : 0
		})
			.then((res) => {
				tags = more ? [...tags, ...res] : res;
				hasMore = res.length === limit;
			})
			.catch((e) => {
				if (!more) tags = [];
				toast.error(e.message || 'Failed to load tags.');
			})
			.finally(() => {
				isLoading = false;
				isLoadingMore = false;
			});
	}

	function handleCreate(e: CustomEvent<Tag>) {
		tags = [e.detail, ...tags];
	}

	function handleDelete(e: CustomEvent<number>) {
		tags = tags.filter((t) => t.id !== e.detail);
	}

	function handleCreateVariant(e: CustomEvent<{ id: number; variant: TagVariant }>) {
		tags = tags.map((t) => {
			const newTag =
				t.id === e.detail.id ? { ...t, variants: [...t.variants, e.detail.variant] } : t;
			return newTag;
		});
	}

	function handleUpdate(e: CustomEvent<Tag>) {
		tags = tags.map((t) => (t.id === e.detail.id ? e.detail : t));
	}

	onMount(() => {
		redirectIfCant('tags.read');
		loadTags();
	});
</script>

<div class="tags">
	<SettingsTop>
		<Button disabled={cant('tags.write')} on:click={() => (isCreating = true)}>
			Create Tag {#snippet end()}
				<IconPlus />
			{/snippet}
		</Button>
	</SettingsTop>

	<div class="note">{i18n.t('console.settings.tags.note')}</div>

	<div class="table">
		{#if isLoading}
			<Loader full />
		{:else if tags.length === 0}
			<IconMessage empty message={i18n.t('console.settings.tags.noTags')} />
		{:else}
			<SettingsTable columns="2fr 2fr 70px">
				<TableRow head>
					<div>{i18n.t('console.posts.filters.tagLabel')}</div>
					<div>{i18n.t('console.settings.tags.slugUrl')}</div>
					<div></div>
				</TableRow>

				{#each tags as tag (tag.id)}
					<TagRow
						{tag}
						on:delete={handleDelete}
						on:variantCreate={handleCreateVariant}
						on:update={handleUpdate}
					/>
				{/each}

				<LoadButton
					text={i18n.t('console.common.loadMore')}
					show={hasMore}
					on:click={() => loadTags(true)}
					loading={isLoadingMore}
				/>
			</SettingsTable>
		{/if}
	</div>
</div>

{#if isCreating}
	<CreateTagModal bind:show={isCreating} on:create={handleCreate} />
{/if}

<style>
	.tags {
		height: 100%;
		display: flex;
		flex-direction: column;
		overflow: auto;
	}

	.note {
		padding: 10px 30px;
		font-size: 14px;
		color: var(--text-light);
	}

	.table {
		flex: 1;
		padding: 15px 30px;
	}
</style>
