<script lang="ts">
	import { Loader, Modal, TextInput, Button, Validation } from '@hyvor/design/components';
	import { createEventDispatcher } from 'svelte';
	import { getUnfold } from '../../../../../../../../lib/actions/urlDataActions';
	import type { UnfoldedEmbed } from '../../../../../../../../lib/types';
	import { IconArrowReturnLeft } from '@hyvor/icons';
	import { isValidUrl } from '../../../../../../../../lib/helper/is-valid-url';
	import EmbedHtmlDisplay from './EmbedHtmlDisplay.svelte';

	let show = true;
	let url = '';

	let inputEl: HTMLInputElement;
	let inputStarted = false;

	const dispatch = createEventDispatcher<{
		close: void;
		create: string;
		createBookmark: string;
		createHtmlBlock: string;
	}>();

	$: if (!show) {
		dispatch('close');
	}

	let isFetching = false;
	let error: null | string = null;

	let urlData: null | UnfoldedEmbed = null;

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

		getUnfold(url, 'embed')
			.then((data) => {
				urlData = data;
			})
			.catch((_) => {
				error = 'Failed to embed this URL';
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
			Fetch <IconArrowReturnLeft slot="end" />
		</Button>
	</div>

	{#if error}
		<div style="margin-top:10px;">
			<Validation state="error">
				{error}
			</Validation>
			<div class="link-alternatives">
				Alternatively, you can add a link link Bookmark or a custom HTML/Twig block and paste the link.
				<div class="alternatives-button">
					<Button on:click={handleCreateBookmark}>
						Create link Bookmark
					</Button>
					<Button on:click={handleCreateHtmlBlock}>
						Create HTML/Twig block
					</Button>
				</div>
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
	}
	.alternatives-button {
		margin-top: 10px;
	}
</style>
