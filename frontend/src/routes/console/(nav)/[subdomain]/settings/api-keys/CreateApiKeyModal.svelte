<script lang="ts">
	import {
		Button,
		InputGroup,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import { createApiKey } from './apiKeysActions';
	import { createEventDispatcher } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';
	import { SCOPES, type Scope } from '../../../../lib/types';
	import { can } from '../../../../lib/scope.svelte';
	import ScopePicker from './ScopePicker.svelte';

	const i18n = getI18n();

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	let api: 'console' | 'delivery' = $state('console');
	let name = $state('');

	// read-only by default
	let scopes: Scope[] = $state(SCOPES.filter((scope) => scope.endsWith('.read') && can(scope)));

	let isButtonDisabled = $derived(
		name.trim().length === 0 || (api === 'console' && scopes.length === 0)
	);

	const dispatch = createEventDispatcher();

	function handleClick() {
		show = false;

		const toastId = toast.loading(i18n.t('console.settings.apiKeys.creating'));

		createApiKey(name, api, api === 'console' ? scopes : [])
			.then((res) => {
				toast.success(i18n.t('console.settings.apiKeys.created'), { id: toastId });
				dispatch('create', res);
			})
			.catch((err) => {
				toast.error(err.message, { id: toastId });
			});
	}
</script>

<Modal
	title={i18n.t('console.settings.apiKeys.create')}
	bind:show
	size={api === 'console' ? 'large' : 'medium'}
>
	<SplitControl label={i18n.t('console.settings.apiKeys.api')}>
		<InputGroup>
			<Radio value="console" bind:group={api}
				>{i18n.t('console.settings.apiKeys.types.console')}</Radio
			>

			<Radio value="delivery" bind:group={api}
				>{i18n.t('console.settings.apiKeys.types.delivery')}</Radio
			>
		</InputGroup>
	</SplitControl>

	<SplitControl
		label={i18n.t('console.common.name')}
		caption={i18n.t('console.common.justForReference')}
	>
		<TextInput
			bind:value={name}
			block
			placeholder={i18n.t('console.settings.apiKeys.namePlaceholder')}
			autofocus
			maxlength={50}
		/>
	</SplitControl>

	{#if api === 'console'}
		<SplitControl
			label={i18n.t('console.settings.apiKeys.scopes')}
			caption={i18n.t('console.settings.apiKeys.scopesCaption')}
			column
		>
			<div class="scopes">
				<ScopePicker bind:selected={scopes} />
			</div>
		</SplitControl>
	{/if}

	{#snippet footer()}
		<Button variant="invisible" on:click={() => (show = false)}
			>{i18n.t('console.common.cancel')}</Button
		>

		<Button on:click={handleClick} disabled={isButtonDisabled}
			>{i18n.t('console.common.create')}</Button
		>
	{/snippet}
</Modal>

<style>
	.scopes {
		max-height: 45vh;
		overflow-y: auto;
	}
</style>
