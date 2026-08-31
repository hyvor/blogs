<script lang="ts">
	import {
		Button,
		IconButton,
		IconMessage,
		LoadButton,
		Loader,
		Table,
		TableRow,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import RedirectsModal from './RedirectsModal.svelte';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import IconX from '@hyvor/icons/IconX';
	import { getRedirect } from './redirectActions';
	import { onMount } from 'svelte';
	import RedirectRow from './RedirectRow.svelte';
	import { dynamicRedirectsStore } from './dynamicRedirect';
	import type { Redirect } from '../../../../lib/types';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	let isCreating = $state(false);

	let redirects: Redirect[] = $state([]);
	let isLoading = $state(true);
	let hasMore = $state(false);
	let isLoadingMore = $state(false);

	const limit = 25;

	let searchVal = $state('');
	let search = $state('');

	const searchActions = {
		onKeydown: (e: KeyboardEvent) => {
			if (e.key === 'Enter') {
				search = searchVal.trim();
				loadRedirect();
			}
			if (e.key === 'Escape') {
				searchActions.onClear();
			}
		},
		onClear: () => {
			searchVal = '';
			search = '';
			loadRedirect();
		}
	};
	function loadRedirect(more = false) {
		more ? (isLoadingMore = true) : (isLoading = true);
		if (!more) redirects = [];

		getRedirect({
			search,
			limit,
			offset: more ? redirects.length : 0
		})
			.then((res) => {
				redirects = more ? [...redirects, ...res] : res;
				dynamicRedirectsStore.set(countDynamicRedirects(redirects));
				hasMore = res.length === limit;
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				isLoading = false;
				isLoadingMore = false;
			});
	}

	function countDynamicRedirects(redirects: Redirect[]) {
		return redirects.filter((r) => r.dynamic).length;
	}

	function handleCreate(e: CustomEvent<Redirect>) {
		redirects = [e.detail, ...redirects];
		dynamicRedirectsStore.set(countDynamicRedirects(redirects));
	}

	function handleDelete(e: CustomEvent<number>) {
		redirects = redirects.filter((t) => t.id !== e.detail);
		dynamicRedirectsStore.set(countDynamicRedirects(redirects));
	}

	function handleUpdate(e: CustomEvent<Redirect>) {
		redirects = redirects.map((t) => (t.id === e.detail.id ? e.detail : t));
		dynamicRedirectsStore.set(countDynamicRedirects(redirects));
	}

	onMount(loadRedirect);
</script>

<SettingsTop>
	<div class="search-wrap">
		<TextInput
			bind:value={searchVal}
			placeholder={i18n.t('console.common.search')}
			style="width:200px;"
			on:keydown={searchActions.onKeydown}
			size="small"
		>
			{#snippet end()}
				{#if searchVal.trim() !== ''}
					<IconButton variant="invisible" color="gray" size={16} on:click={searchActions.onClear}>
						<IconX size={12} />
					</IconButton>
				{/if}
			{/snippet}
		</TextInput>

		{#if search !== searchVal}
			<span class="press-enter"> ⏎ </span>
		{/if}
	</div>

	<Button size="small" on:click={() => (isCreating = true)}>
		{i18n.t('console.settings.redirects.add')}
		{#snippet end()}
			<IconPlus />
		{/snippet}
	</Button>
</SettingsTop>

<div class="redirects">
	{#if isLoading}
		<Loader full />
	{:else if redirects.length === 0}
		<IconMessage empty message={i18n.t('console.settings.redirects.noRedirects')} />
	{:else}
		<Table columns="1fr 2fr 1fr 70px">
			<TableRow head>
				<div>{i18n.t('console.settings.redirects.from')}</div>
				<div>{i18n.t('console.settings.redirects.to')}</div>
				<div>{i18n.t('console.settings.redirects.type')}</div>
				<div></div>
			</TableRow>

			{#each redirects as redirect (redirect.id)}
				<RedirectRow {redirect} on:delete={handleDelete} on:update={handleUpdate} />
			{/each}

			<LoadButton
				text={i18n.t('console.common.loadMore')}
				show={hasMore}
				loading={isLoadingMore}
				on:click={() => loadRedirect(true)}
			/>
		</Table>
	{/if}
</div>

{#if isCreating}
	<RedirectsModal bind:show={isCreating} on:create={handleCreate} on:updated={handleUpdate} />
{/if}

<style lang="scss">
	.redirects {
		padding: 15px 30px;
		flex: 1;
		overflow: auto;
	}
	.search-wrap {
		display: flex;
		margin-right: 6px;
		.press-enter {
			color: var(--text-light);
			font-size: 14px;
			margin-left: 4px;
			margin-top: 6px;
		}
		:global(input) {
			font-size: 14px;
		}
	}
</style>
