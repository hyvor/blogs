<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { uploadImageOnly } from '../../../../lib/fileUploader';
	import { createEventDispatcher } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		src?: string | null;
		uploadText?: string;
	}

	let { src = null, uploadText = 'Upload' }: Props = $props();

	const dispatch = createEventDispatcher<{ change: string | null }>();

	async function handleUpload() {
		const file = await uploadImageOnly();
		if (file) {
			dispatch('change', file.url);
		}
	}

	function handleRemove() {
		dispatch('change', null);
	}
</script>

{#if !src}
	<Button size="small" on:click={handleUpload}>
		{uploadText}
	</Button>
{:else}
	<div class="img-wrap">
		<img {src} alt={i18n.t('console.tools.media.uploaded')} />
	</div>

	<div class="buttons">
		<Button on:click={handleUpload} size="x-small" variant="fill-light">
			{i18n.t('console.theme.change')}
		</Button>
		<Button on:click={handleRemove} size="x-small" color="red" variant="fill-light">
			{i18n.t('console.common.remove')}
		</Button>
	</div>
{/if}

<style>
	img {
		max-width: 250px;
		max-height: 250px;
		border-radius: 5px;
	}
	.buttons {
		margin-top: 5px;
		display: flex;
		gap: 2px;
	}
</style>
