<script lang="ts">
	import {
		Button,
		ButtonGroup,
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

	const i18n = getI18n();

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	let api: 'console' | 'delivery' = $state('console');
	let name = $state('');

	let isButtonDisabled = $derived(name.trim().length === 0);

	const dispatch = createEventDispatcher();

	function handleClick() {
		show = false;

		const toastId = toast.loading(i18n.t('console.settings.apiKeys.creating'));

		createApiKey(name, api)
			.then((res) => {
				toast.success(i18n.t('console.settings.apiKeys.created'), { id: toastId });
				dispatch('create', res);
			})
			.catch((err) => {
				toast.error(err.message, { id: toastId });
			});
	}
</script>

<Modal title={i18n.t('console.settings.apiKeys.create')} bind:show>
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

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (show = false)}
				>{i18n.t('console.common.cancel')}</Button
			>

			<Button on:click={handleClick} disabled={isButtonDisabled}
				>{i18n.t('console.common.create')}</Button
			>
		</ButtonGroup>
	{/snippet}
</Modal>
