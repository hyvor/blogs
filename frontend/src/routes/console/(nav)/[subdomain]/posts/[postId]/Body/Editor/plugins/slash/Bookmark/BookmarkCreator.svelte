<script lang="ts">
	import { run } from 'svelte/legacy';

	import { Loader, Modal, TextInput, Button, Validation } from '@hyvor/design/components';
	import { createEventDispatcher, onMount } from 'svelte';
	import { getUnfold } from '../../../../../../../../../lib/actions/urlDataActions';
	import type { UnfoldedLink } from '../../../../../../../../../lib/types';
	import { IconArrowReturnLeft } from '@hyvor/icons';
	import { isValidUrl } from '../../../../../../../../../lib/helper/is-valid-url';
	import BookmarkDisplay from './BookmarkDisplay.svelte';

	let show = $state(true);

	interface Props {
		url?: string;
	}

	let { url = $bindable('') }: Props = $props();

	let inputEl: HTMLInputElement = $state();
	let inputStarted = $state(false);

	const dispatch = createEventDispatcher<{
		close: void;
		create: string;
	}>();

	run(() => {
		if (!show) {
			dispatch('close');
		}
	});

	let isFetching = $state(false);
	let error: null | string = $state(null);

	let urlData: null | UnfoldedLink = $state(null);

	function handleFetch() {
		if (!inputStarted) {
			return;
		}

		error = null;
		urlData = null;

		if (url.trim() === '') {
			error = 'URL is required';
			inputEl.focus();
			return;
		}

		if (!isValidUrl(url)) {
			error = 'Invalid URL';
			inputEl.focus();
			return;
		}

		isFetching = true;

		getUnfold(url, 'link')
			.then((data) => {
				urlData = data;
			})
			.catch((_) => {
				error = 'Failed to load URL';
			})
			.finally(() => {
				isFetching = false;
			});
	}

	function handleCreate() {
		dispatch('create', urlData!.url);
	}

	onMount(() => {
		if (url !== '') {
			inputStarted = true;
			handleFetch();
		}
	});
</script>

<Modal
	bind:show
	title="Create Bookmark"
	footer={{
		confirm: urlData
			? {
					text: 'Create Bookmark'
				}
			: false,
		cancel: {
			text: 'Close'
		}
	}}
	on:confirm={handleCreate}
>
	<div class="input-wrap">
		<TextInput
			placeholder="Enter any URL..."
			autofocus
			block
			bind:value={url}
			on:keyup={(e) => {
				if (e.key === 'Enter') {
					handleFetch();
				} else {
					inputStarted = true;
				}
			}}
			state={error ? 'error' : undefined}
			bind:input={inputEl}
		/>
		<Button on:click={handleFetch}>
			Fetch {#snippet end()}
						<IconArrowReturnLeft  />
					{/snippet}
		</Button>
	</div>

	{#if error}
		<div style="margin-top:10px;">
			<Validation state="error">
				{error}
			</Validation>
		</div>
	{/if}

	{#if isFetching}
		<Loader block padding={50} />
	{/if}

	{#if urlData}
		<div class="display">
			<BookmarkDisplay link={urlData} />
		</div>
	{/if}
</Modal>

<style>
	.input-wrap {
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.display {
		margin-top: 20px;
		overflow: auto;
		max-height: 400px;
	}
</style>
