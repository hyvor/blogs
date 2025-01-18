<script lang="ts">
	import { run } from 'svelte/legacy';

	import { Loader, Modal, TextInput, Button, Validation } from '@hyvor/design/components';
	import { createEventDispatcher } from 'svelte';
	import { getUnfold } from '../../../../../../../../../lib/actions/urlDataActions';
	import type { UnfoldedEmbed } from '../../../../../../../../../lib/types';
	import { IconArrowReturnLeft } from '@hyvor/icons';
	import { isValidUrl } from '../../../../../../../../../lib/helper/is-valid-url';
	import EmbedHtmlDisplay from './EmbedHtmlDisplay.svelte';

	let show = $state(true);
	let url = $state('');

	let inputEl: HTMLInputElement = $state();
	let inputStarted = $state(false);

	const dispatch = createEventDispatcher<{
		close: void;
		create: string;
		createBookmark: string;
		createHtmlBlock: string;
	}>();

	run(() => {
		if (!show) {
			dispatch('close');
		}
	});

	let isFetching = $state(false);
	let error: null | string = $state(null);
	let embedFailed = $state(false);

	let urlData: null | UnfoldedEmbed = $state(null);

	function handleFetch() {
		if (!inputStarted) {
			return;
		}

		error = null;
		urlData = null;
		embedFailed = false;

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

		getUnfold(url, 'embed')
			.then((data) => {
				urlData = data;
			})
			.catch((_) => {
				error = 'Failed to embed this URL';
				embedFailed = true;
			})
			.finally(() => {
				isFetching = false;
			});
	}

	function handleCreate() {
		dispatch('create', url);
	}

	function handleCreateBookmark(): void {
		dispatch('createBookmark', url);
	}

	function handleCreateHtmlBlock(): void {
		dispatch('createHtmlBlock', url);
	}
</script>

<Modal
	bind:show
	title="Create Embed"
	footer={{
		confirm: urlData
			? {
					text: 'Create Embed'
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
			placeholder="Enter URL from YouTube, Twitter, etc."
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

	{#if embedFailed}
		<div class="link-alternatives">
			We couldn't convert this URL to an embed. You can add a link bookmark to preview the URL or
			create a custom HTML block and paste the embed code manually.
			<div class="alternatives-button">
				<Button variant="outline" color="gray" size="small" on:click={handleCreateBookmark}
					>Create Link Bookmark</Button
				>
				<Button variant="outline" color="gray" size="small" on:click={handleCreateHtmlBlock}
					>Create Custom HTML</Button
				>
			</div>
		</div>
	{/if}

	{#if isFetching}
		<Loader block padding={50} />
	{/if}

	{#if urlData}
		<div class="display">
			<EmbedHtmlDisplay url={urlData.url} />
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
	.link-alternatives {
		margin-top: 10px;
		padding: 20px 25px;
		background-color: var(--red-light);
		border-radius: 20px;
	}
	.alternatives-button {
		margin-top: 10px;
	}
</style>
