<script lang="ts">
	import UnsavedTag from './UnsavedTag.svelte';
	import { FormControl, SplitControl, TextInput, Validation } from '@hyvor/design/components';
	import {
		postVariantOriginalStore,
		postStore,
		postVariantStore,
		updatePostVariantStore
	} from '../../../postStore';
	import { onMount } from 'svelte';
	import consoleApi from '../../../../../../lib/consoleApi';
	import { updatePostVariant } from '../../../postActions';

	import LabelWithInfo from './LabelWithInfo.svelte';
	import { slugGetInvalidCharater } from './slug';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	let error: null | string = $state(null);
	let warning: null | string = $state(null);

	let loaderState: 'none' | 'loading' | 'success' | 'error' = $state('none');

	function setErrorWarning(val: string) {
		error = null;
		warning = null;

		val = val.trim();

		const invalidChar = slugGetInvalidCharater(val);

		if (invalidChar) {
			error = i18n.t('console.postEditor.settings.slugInvalidChar', { char: invalidChar });
		} else if (val.includes(' ')) {
			warning = i18n.t('console.postEditor.settings.slugSpaceTip');
		}
	}

	function handleInput(e: any) {
		let val = e.target.value as string;
		updatePostVariantStore({ slug: val });
		setErrorWarning(val);
	}

	function handleBlur(e: any) {
		const slug = (e.target.value as string).trim();

		if (!slug) return;
		if (slug === $postVariantOriginalStore.slug) return;
		if (error) return;

		loaderState = 'loading';

		consoleApi
			.get<{ available: boolean }>({
				endpoint: `/post/${$postStore.id}/slug-available`,
				data: {
					language_id: $postVariantStore.language_id,
					slug
				}
			})
			.then((res) => {
				if (!res.available) {
					loaderState = 'error';
					error = i18n.t('console.postEditor.settings.slugTaken');
				} else {
					if ($postVariantStore.status !== 'published') {
						updatePostVariant({ slug })
							.catch((err) => {
								loaderState = 'error';
								error = err.message;
							})
							.finally(() => {
								loaderState = 'success';
							});
					} else {
						loaderState = 'success';
					}
				}
			})
			.catch(() => {
				loaderState = 'error';
			});
	}

	onMount(() => {
		setErrorWarning($postVariantStore.slug || '');
	});
</script>

<SplitControl>
	{#snippet label()}
		<span>
			<LabelWithInfo
				label={i18n.t('console.postEditor.settings.slug')}
				info={i18n.t('console.postEditor.settings.slugInfo')}
			/>

			<UnsavedTag show={$postVariantStore.slug !== $postVariantOriginalStore.slug} {loaderState} />
		</span>
	{/snippet}

	<FormControl>
		<TextInput
			block
			value={$postVariantStore.slug}
			on:input={handleInput}
			on:blur={handleBlur}
			maxlength={255}
			state={error ? 'error' : warning ? 'warning' : 'default'}
		/>

		{#if error}
			<Validation state="error">{error}</Validation>
		{/if}

		{#if warning}
			<Validation state="warning">{warning}</Validation>
		{/if}
	</FormControl>
</SplitControl>
