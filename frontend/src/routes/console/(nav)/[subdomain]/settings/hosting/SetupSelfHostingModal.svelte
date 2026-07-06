<script lang="ts">
	import { onMount } from 'svelte';
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

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	let url = $state('');
	let error: string | null = $state(null);
	let loading = $state(false);

	let urlInput: HTMLInputElement;

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
			error = 'Self-Hosting URL is required';
			return;
		}
		if (!isValidUrl(urlTrimmed)) {
			error = 'Invalid URL. Make sure to include the protocol (https:// or http://)';
			return;
		}

		loading = true;
		const toastId = toast.loading('Saving self-hosting settings...');

		try {
			const updates = await updateHostedAt('self', urlTrimmed);
			updateHostingInfoStore(updates);
			toast.success('Self-hosting configuration saved successfully!', { id: toastId });
			show = false;
		} catch (err: any) {
			toast.error(err.message || 'Failed to save hosting URL', { id: toastId });
		} finally {
			loading = false;
		}
	}
</script>

<Modal title="Setup Self-hosting" {loading} bind:show>
	<SplitControl label="Self-hosting URL" caption="Where your blog is hosted (absolute URL)">
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
				>Cancel</Button
			>
			<Button on:click={handleConfirm} disabled={loading}>Confirm</Button>
		</ButtonGroup>
	{/snippet}
</Modal>
