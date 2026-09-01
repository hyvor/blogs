<script lang="ts">
	import {
		Button,
		ButtonGroup,
		FormControl,
		Modal,
		SplitControl,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import { isValidUrl } from '../../../../lib/helper/is-valid-url';
	import { blogStore, updateHostingInfoStore } from '../../../../lib/stores/blogStore';
	import { updateHostedAt } from './hostingActions';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	let url = $state('');
	let error: string | null = $state(null);
	let loading = $state(false);

	let urlInput: HTMLInputElement;

	let isChangingExisting = $derived($blogStore.hosting_at === 'self');

	$effect(() => {
		if (show) {
			url = $blogStore.hosting_url || '';
			error = null;
			urlInput?.focus();
		}
	});

	async function handleConfirm() {
		error = null;
		const urlTrimmed = url.trim();

		if (urlTrimmed === '') {
			error = i18n.t('console.settings.hosting.validation.selfUrlRequired');
			return;
		}
		if (!isValidUrl(urlTrimmed)) {
			error = 'Invalid URL. Make sure to include the protocol (https:// or http://)';
			return;
		}

		loading = true;

		try {
			const updates = await updateHostedAt('self', urlTrimmed);
			updateHostingInfoStore(updates);
			toast.info(
				isChangingExisting
					? 'Updating your self-serving URL. It may take a few minutes to complete.'
					: 'Changing your hosting to self-serving. It may take a few minutes to complete.'
			);
			show = false;
		} catch (err: any) {
			toast.error(err.message || 'Failed to save hosting URL');
		} finally {
			loading = false;
		}
	}
</script>

<Modal
	title={isChangingExisting
		? i18n.t('console.settings.hosting.changeSelfServingUrlTitle')
		: i18n.t('console.settings.hosting.setupSelfHostingTitle')}
	{loading}
	bind:show
>
	<SplitControl
		label={i18n.t('console.settings.hosting.selfUrl')}
		caption={i18n.t('console.settings.hosting.selfUrlCaption')}
	>
		<FormControl>
			<TextInput
				bind:value={url}
				placeholder="https://example.com/blog"
				block
				state={error ? 'error' : undefined}
				bind:input={urlInput}
			/>
			{#if error}
				<Validation state="error">{error}</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (show = false)} disabled={loading}
				>{i18n.t('console.common.cancel')}</Button
			>
			<Button on:click={handleConfirm} disabled={loading}
				>{isChangingExisting
					? i18n.t('console.common.update')
					: i18n.t('console.settings.hosting.confirm')}</Button
			>
		</ButtonGroup>
	{/snippet}
</Modal>
