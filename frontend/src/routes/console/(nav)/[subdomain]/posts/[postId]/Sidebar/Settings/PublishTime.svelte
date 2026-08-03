<script lang="ts">
	import { SplitControl, Text, TextInput } from '@hyvor/design/components';
	import {
		postOriginalStore,
		postVariantOriginalStore,
		postStore,
		postVariantStore,
		updatePostStore
	} from '../../../postStore';
	import UnsavedTag from './UnsavedTag.svelte';
	import { updatePost } from '../../../postActions';
	import OnlyPrimaryVariant from './OnlyPrimaryVariant.svelte';
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
		updatePostStore({
			published_at: val ? getUnixTimestamp(val) : null
		});
	}

	function handleBlur(e: any) {
		const val = e.target.value;
		const timestamp = val ? getUnixTimestamp(val) : null;

		if (timestamp === $postOriginalStore.published_at) return;

		if ($postVariantStore.status !== 'published') {
			loaderState = 'loading';

			updatePost({ published_at: timestamp })
				.then(() => {
					loaderState = 'success';
				})
				.catch((err) => {
					loaderState = 'error';
				});
		}
	}
</script>

<OnlyPrimaryVariant>
	<SplitControl>
		{#snippet label()}
			<span>
				Publish Time

				<UnsavedTag
					show={$postStore.published_at !== $postOriginalStore.published_at}
					{loaderState}
				/>
			</span>
		{/snippet}

		{#if $postVariantStore.status !== 'draft' || $postStore.published_at !== null}
			<TextInput
				block
				type="datetime-local"
				value={timestampToDateTime($postStore.published_at)}
				on:input={handleInput}
				on:blur={handleBlur}
			/>
		{:else}
			<Text small light>Not published yet</Text>
		{/if}
	</SplitControl>
</OnlyPrimaryVariant>
