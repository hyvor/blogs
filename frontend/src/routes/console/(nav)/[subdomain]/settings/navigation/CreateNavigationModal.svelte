<script lang="ts">
	import {
		Button,
		ButtonGroup,
		FormControl,
		InputGroup,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import type { Redirect } from '../../../../lib/types';
	import { createNavigation } from './navigationActions';
	import { createEventDispatcher } from 'svelte';
	import { isValidUrl } from '../../../../lib/helper/is-valid-url';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		show?: boolean;
	}

	let { show = $bindable(false) }: Props = $props();
	let loading = $state(false);

	const dispatch = createEventDispatcher();

	let name = $state('');
	let url = $state('');
	let type: 'header' | 'footer' = $state('header');

	let nameError: null | string = $state(null);
	let urlError: null | string = $state(null);

	function handleClick() {
		nameError = null;
		urlError = null;

		if (!name) {
			nameError = i18n.t('console.common.nameRequired');
			return;
		}

		if (!url) {
			urlError = i18n.t('console.settings.navigation.urlRequired');
			return;
		}

		loading = true;
		createNavigation(name, url, type)
			.then((res) => {
				toast.success(i18n.t('console.settings.navigation.created'));
				dispatch('create', res);
				show = false;
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				loading = false;
			});
	}

	let isButtonDisabled = $derived(name == '' || url == '');
</script>

<Modal title={'Add new navigation'} {loading} bind:show>
	<SplitControl label={i18n.t('console.common.name')}>
		<FormControl>
			<TextInput
				bind:value={name}
				placeholder={i18n.t('console.settings.navigation.namePlaceholder')}
				block
				state={nameError ? 'error' : undefined}
				autofocus
			/>

			{#if nameError}
				<Validation state="error">
					{nameError}
				</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl label={i18n.t('console.tools.import.url')}>
		<FormControl>
			<TextInput
				bind:value={url}
				placeholder="/about"
				block
				state={urlError ? 'error' : undefined}
			/>

			{#if urlError}
				<Validation state="error">
					{urlError}
				</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl label={i18n.t('console.tools.import.type')}>
		<InputGroup>
			<Radio name="type" value="header" bind:group={type}
				>{i18n.t('console.settings.navigation.header')}</Radio
			>
			<Radio name="type" value="footer" bind:group={type}
				>{i18n.t('console.settings.navigation.footer')}</Radio
			>
		</InputGroup>
	</SplitControl>

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (show = false)}
				>{i18n.t('console.common.cancel')}</Button
			>

			<Button on:click={handleClick} disabled={isButtonDisabled}
				>{i18n.t('console.common.add')}</Button
			>
		</ButtonGroup>
	{/snippet}
</Modal>
