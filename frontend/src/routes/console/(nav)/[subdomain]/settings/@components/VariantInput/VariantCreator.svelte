<script lang="ts">
	import { Button, Loader, toast } from '@hyvor/design/components';
	import type { Language } from '../../../../../lib/types';
	import type { AcceptableTypes, AcceptableTypesNames } from './VariantInput.svelte';
	import { createTagVariant } from '../../tags/tagActions';
	import { createEventDispatcher } from 'svelte';
	import { createBlogVariant } from '../../../../../lib/actions/blogActions';
	import { createNavigationVariant } from '../../navigation/navigationActions';
	import { createUserVariant } from '../../users/userActions';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		obj: AcceptableTypes;
		type: AcceptableTypesNames;
		language: Language;
	}

	let { obj, type, language }: Props = $props();

	let isCreating = $state(false);

	const dispatch = createEventDispatcher();

	function dispatchCreateVariant(variant: any) {
		dispatch('variantCreate', {
			id: obj.id,
			variant
		});
	}

	function handleCreate(e: any) {
		e.stopPropagation();

		isCreating = true;

		if (type === 'tag') {
			createTagVariant(obj.id, language.id)
				.then((res) => {
					dispatchCreateVariant(res);
				})
				.catch((e) => {
					toast.error(e.message);
				})
				.finally(() => {
					isCreating = false;
				});
		} else if (type === 'blog') {
			createBlogVariant(language.id, true)
				.then((res) => {
					dispatchCreateVariant(res);
				})
				.catch((e) => {
					toast.error(e.message);
				})
				.finally(() => {
					isCreating = false;
				});
		} else if (type == 'navigation') {
			createNavigationVariant(obj.id, language.id)
				.then((res) => {
					dispatchCreateVariant(res);
				})
				.catch((e) => {
					toast.error(e.message);
				})
				.finally(() => {
					isCreating = false;
				});
		} else if (type === 'user') {
			createUserVariant(obj.id, language.id)
				.then((res) => {
					dispatchCreateVariant(res);
				})
				.catch((e) => {
					toast.error(e.message);
				})
				.finally(() => {
					isCreating = false;
				});
		}
	}
</script>

{#if isCreating}
	<Loader />
{:else}
	<Button size="small" on:click={handleCreate}>
		{i18n.t('console.settings.variant.create', { language: language.name })}
	</Button>

	<div>
		{i18n.t('console.settings.variant.hint', { language: language.name, type })}
	</div>
{/if}

<style>
	div {
		margin-top: 5px;
		font-size: 12px;
		color: var(--text-light);
	}
</style>
