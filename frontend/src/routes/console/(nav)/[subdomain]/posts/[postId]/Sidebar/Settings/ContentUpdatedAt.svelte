<script lang="ts">
	import { SplitControl, TextInput, Validation } from '@hyvor/design/components';
	import {
		postVariantOriginalStore,
		postVariantStore,
		updatePostVariantStore
	} from '../../../postStore';
	import UnsavedTag from './UnsavedTag.svelte';
	import { updatePostVariant } from '../../../postActions';
	import dayjs from 'dayjs';

	let loaderState: 'none' | 'loading' | 'success' | 'error' = $state('none');

	function getUnixTimestamp(date: string) {
		return Math.floor(new Date(date).getTime() / 1000);
	}

	function timestampToDateTime(timestamp: number | null) {
		if (!timestamp) return '';
		return dayjs.unix(timestamp).format('YYYY-MM-DDTHH:mm');
	}

	function handleInput(e: any) {
		const val = e.target.value;
		updatePostVariantStore({
			content_updated_at: val ? getUnixTimestamp(val) : null
		});
	}

	let isTooEarly = $derived(
		$postVariantStore.content_updated_at !== null &&
			$postVariantStore.published_at !== null &&
			$postVariantStore.content_updated_at < $postVariantStore.published_at
	);

	function handleBlur(e: any) {
		const val = e.target.value;
		const timestamp = val ? getUnixTimestamp(val) : null;

		if (timestamp === $postVariantOriginalStore.content_updated_at) return;
		if (isTooEarly) return;

		loaderState = 'loading';
		updatePostVariant({ content_updated_at: timestamp })
			.then(() => {
				loaderState = 'success';
			})
			.catch(() => {
				loaderState = 'error';
			});
	}
</script>

{#if $postVariantStore.status !== 'draft'}
	<SplitControl>
		{#snippet label()}
			<span>
				Content Updated At

				<UnsavedTag
					show={$postVariantStore.content_updated_at !==
						$postVariantOriginalStore.content_updated_at}
					{loaderState}
				/>
			</span>
		{/snippet}

		<TextInput
			block
			type="datetime-local"
			value={timestampToDateTime($postVariantStore.content_updated_at)}
			on:input={handleInput}
			on:blur={handleBlur}
		/>

		{#if isTooEarly}
			<div style="margin-top:5px;">
				<Validation state="error">Must be on or after the publish time.</Validation>
			</div>
		{/if}
	</SplitControl>
{/if}
