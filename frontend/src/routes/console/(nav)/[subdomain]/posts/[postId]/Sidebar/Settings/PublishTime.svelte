<script lang="ts">
	import { SplitControl, Text, TextInput } from '@hyvor/design/components';
	import {
		postVariantOriginalStore,
		postVariantStore,
		updatePostVariantStore
	} from '../../../postStore';
	import UnsavedTag from './UnsavedTag.svelte';
	import { updatePostVariant } from '../../../postActions';
	import OnlyPrimaryVariant from './OnlyPrimaryVariant.svelte';
	import dayjs from 'dayjs';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

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
			published_at: val ? getUnixTimestamp(val) : null
		});
	}

	function handleBlur(e: any) {
		const val = e.target.value;
		const timestamp = val ? getUnixTimestamp(val) : null;

		if (timestamp === $postVariantOriginalStore.published_at) return;

		if ($postVariantStore.status !== 'published') {
			loaderState = 'loading';

			updatePostVariant({ published_at: timestamp })
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
				{i18n.t('console.postEditor.settings.publishTime')}
				{#if $postVariantStore.status === 'scheduled'}
					{i18n.t('console.postEditor.settings.publishTimeScheduled')}
				{/if}

				<UnsavedTag
					show={$postVariantStore.published_at !== $postVariantOriginalStore.published_at}
					{loaderState}
				/>
			</span>
		{/snippet}

		{#if $postVariantStore.status !== 'draft'}
			<TextInput
				block
				type="datetime-local"
				value={timestampToDateTime($postVariantStore.published_at)}
				on:input={handleInput}
				on:blur={handleBlur}
			/>
		{:else}
			<Text small light>{i18n.t('console.postEditor.settings.notPublishedYet')}</Text>
		{/if}
	</SplitControl>
</OnlyPrimaryVariant>
