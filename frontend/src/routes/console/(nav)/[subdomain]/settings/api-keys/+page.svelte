<script lang="ts">
	import ApiKeyRow from './ApiKeyRow.svelte';
	import { Button, IconMessage, Loader, TableRow, toast } from '@hyvor/design/components';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import SettingsTable from '../@components/SettingsTable.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import CreateApiKeyModal from './CreateApiKeyModal.svelte';
	import type { ApiKey } from '../../../../lib/types';
	import { onMount } from 'svelte';
	import { getApiKeys } from './apiKeysActions';
	import { getI18n } from '../../../../lib/i18n';
	import { cant, redirectIfCant } from '../../../../lib/scope.svelte';

	const i18n = getI18n();

	let isCreating = $state(false);

	let isLoading = $state(true);
	let apiKeys: ApiKey[] = $state([]);

	function handleCreate(e: CustomEvent<ApiKey>) {
		apiKeys = [e.detail, ...apiKeys];
	}

	function handleDeleteEvent(e: CustomEvent<number>) {
		apiKeys = apiKeys.filter((apiKey) => apiKey.id !== e.detail);
	}

	function handleUpdateEvent(e: CustomEvent<ApiKey>) {
		apiKeys = apiKeys.map((apiKey) => {
			if (apiKey.id === e.detail.id) {
				return e.detail;
			}
			return apiKey;
		});
	}

	onMount(() => {
		redirectIfCant('api_keys.read');
		getApiKeys()
			.then((res) => {
				apiKeys = res;
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				isLoading = false;
			});
	});
</script>

<div class="api-keys">
	<SettingsTop>
		<Button disabled={cant('api_keys.write')} on:click={() => (isCreating = true)}>
			Create API Key {#snippet end()}
				<IconPlus />
			{/snippet}
		</Button>
	</SettingsTop>

	<div class="table">
		{#if isLoading}
			<Loader full />
		{:else if apiKeys.length === 0}
			<IconMessage empty message={i18n.t('console.settings.apiKeys.noKeys')} />
		{:else}
			<SettingsTable columns="1fr 1fr 140px">
				<TableRow head>
					<div>{i18n.t('console.common.name')}</div>
					<div>{i18n.t('console.settings.apiKeys.api')}</div>
					<div></div>
				</TableRow>

				{#each apiKeys as apiKey (apiKey.id)}
					<ApiKeyRow {apiKey} on:delete={handleDeleteEvent} on:update={handleUpdateEvent} />
				{/each}
			</SettingsTable>
		{/if}
	</div>
</div>

{#if isCreating}
	<CreateApiKeyModal bind:show={isCreating} on:create={handleCreate} />
{/if}

<style>
	.api-keys {
		height: 100%;
		display: flex;
		flex-direction: column;
		overflow: auto;
	}

	.table {
		flex: 1;
		padding: 15px 30px;
	}
</style>
