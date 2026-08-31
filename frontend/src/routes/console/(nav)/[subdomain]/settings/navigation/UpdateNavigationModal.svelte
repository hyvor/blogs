<script lang="ts">
	import {
		Button,
		FormControl,
		InputGroup,
		Link,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import type { Navigation, NavigationVariant } from '../../../../lib/types';
	import VariantInput from '../@components/VariantInput/VariantInput.svelte';
	import { updateNagivation, updateNavigationVariant } from './navigationActions';
	import { createEventDispatcher } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		show?: boolean;
		navigation: Navigation;
	}

	let { show = $bindable(false), navigation }: Props = $props();

	let url = $state(navigation.url);
	let type = $state(navigation.type);

	let urlError: null | string = $state(null);

	const variantChanges: Record<number, Partial<NavigationVariant>> = $state({});

	function handleNameChange(e: CustomEvent<{ languageId: number; value: string }>) {
		variantChanges[e.detail.languageId] = {
			name: e.detail.value
		};
	}

	let hasChanges = $derived(
		Object.keys(variantChanges).length !== 0 || url !== navigation.url || type !== navigation.type
	);

	const dispatch = createEventDispatcher();

	let isUpdating = $state(false);

	async function handleUpdate() {
		if (!url) {
			urlError = i18n.t('console.settings.navigation.urlRequired');
			return;
		}

		isUpdating = true;

		const toastId = toast.loading(i18n.t('console.settings.navigation.updating'));
		for (const [languageId, changes] of Object.entries(variantChanges)) {
			try {
				await updateNavigationVariant(navigation.id, Number(languageId), changes, type, url);
			} catch (e) {
				toast.error(i18n.t('console.settings.navigation.failedToUpdateVariant'), { id: toastId });
				isUpdating = false;
				return;
			}
		}

		const updates: Partial<Navigation> = {};
		updates.url = url;
		updates.type = type;

		if (url !== navigation.url) {
			updates.url = url;
		}

		if (type !== navigation.type) {
			updates.type = type;
		}

		updateNagivation(navigation.id, updates)
			.then((res) => {
				toast.success(i18n.t('console.settings.navigation.updated'), { id: toastId });
				show = false;
				dispatch('update', res);
			})
			.catch((e) => {
				toast.error(i18n.t('console.settings.navigation.failedToUpdate'), { id: toastId });
			})
			.finally(() => {
				isUpdating = false;
			});
	}
</script>

<Modal title={i18n.t('console.settings.navigation.editNavigation')} bind:show>
	<VariantInput
		obj={navigation}
		type="navigation"
		key="name"
		label={i18n.t('console.common.name')}
		caption={i18n.t('console.settings.navigation.nameCaption')}
		on:variantCreate
		on:change={handleNameChange}
	/>

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
		<Button variant="invisible" on:click={() => (show = false)}
			>{i18n.t('console.common.cancel')}</Button
		>

		<Button on:click={handleUpdate} disabled={!hasChanges || isUpdating}
			>{i18n.t('console.common.update')}</Button
		>
	{/snippet}
</Modal>
