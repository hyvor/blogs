<script lang="ts">
	import {
		Button,
		Caption,
		FormControl,
		Link,
		Modal,
		SplitControl,
		Switch,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import type { Tag, TagVariant } from '../../../../../lib/types';
	import CodemirrorEditor from '../../../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte';
	import IconCaretDownFill from '@hyvor/icons/IconCaretDownFill';
	import IconCaretRightFill from '@hyvor/icons/IconCaretRightFill';

	import VariantInput from '../../@components/VariantInput/VariantInput.svelte';
	import { updateTag, updateTagVariant } from '../tagActions';
	import { createEventDispatcher } from 'svelte';
	import TagSlug from './TagSlug.svelte';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	interface Props {
		show?: boolean;
		tag: Tag;
	}

	let { show = $bindable(false), tag }: Props = $props();

	let isPrivate = $state(tag.is_private);
	let slug = $state(tag.slug);
	let codeHead = $state(tag.code_head || '');
	let codeFoot = tag.code_foot || '';

	let customCode = $state(false);

	const variantChanges: Record<number, Partial<TagVariant>> = $state({});

	function handleNameChange(e: CustomEvent<{ languageId: number; value: string }>) {
		variantChanges[e.detail.languageId] = {
			...(variantChanges[e.detail.languageId] || {}),
			name: e.detail.value
		};
	}

	function handleDescriptionChange(e: CustomEvent<{ languageId: number; value: string }>) {
		variantChanges[e.detail.languageId] = {
			description: e.detail.value
		};
	}

	let hasChanges = $derived(
		Object.keys(variantChanges).length !== 0 ||
			slug !== tag.slug ||
			codeHead !== (tag.code_head || '') ||
			codeFoot !== (tag.code_foot || '')
	);

	const dispatch = createEventDispatcher();

	let isUpdating = $state(false);

	async function handleUpdate() {
		isUpdating = true;

		const toastId = toast.loading(i18n.t('console.settings.tags.updating'));

		for (const [languageId, changes] of Object.entries(variantChanges)) {
			try {
				await updateTagVariant(tag.id, Number(languageId), changes);
			} catch (e) {
				toast.error(i18n.t('console.settings.tags.failedToUpdateVariant'), { id: toastId });
				isUpdating = false;
				return;
			}
		}

		const updates: Partial<Tag> = {};

		if (isPrivate !== tag.is_private) {
			updates.is_private = isPrivate;
		}
		if (slug !== tag.slug) {
			updates.slug = slug;
		}
		if (codeHead !== (tag.code_head || '')) {
			updates.code_head = codeHead;
		}
		if (codeFoot !== (tag.code_foot || '')) {
			updates.code_foot = codeFoot;
		}

		updateTag(tag.id, updates)
			.then((res) => {
				toast.success(i18n.t('console.settings.tags.updated'), { id: toastId });
				show = false;
				dispatch('update', res);
			})
			.catch((e) => {
				toast.error(i18n.t('console.settings.tags.failedToUpdate'), { id: toastId });
			})
			.finally(() => {
				isUpdating = false;
			});
	}
</script>

<Modal title={i18n.t('console.settings.tags.editTag')} bind:show>
	<SplitControl label={i18n.t('console.settings.tags.private')}>
		{#snippet caption()}
			<Caption>
				<T
					key="console.settings.tags.privateCaption"
					params={{
						link: {
							element: 'a',
							props: { href: '/docs/tags#private', target: '_blank', class: 'hds-link' }
						}
					}}
				/>
			</Caption>
		{/snippet}
		<Switch bind:checked={isPrivate} />
	</SplitControl>

	<VariantInput
		obj={tag}
		type="tag"
		key="name"
		label={i18n.t('console.common.name')}
		caption={i18n.t('console.settings.tags.nameCaption')}
		maxlength={255}
		on:variantCreate
		on:change={handleNameChange}
	/>

	<VariantInput
		obj={tag}
		type="tag"
		key="description"
		label={i18n.t('console.tools.import.description')}
		caption={i18n.t('console.settings.tags.descriptionCaption')}
		maxlength={255}
		on:variantCreate
		on:change={handleDescriptionChange}
	/>

	<TagSlug id={tag.id} bind:slug slugOriginal={tag.slug} />

	<div class="custom-code-wrap">
		<Link
			href="javascript:void()"
			on:click={(e) => {
				e.stopPropagation();
				customCode = !customCode;
			}}
		>
			{i18n.t('console.settings.nav.code')}

			{#snippet end()}
				<span>
					{#if customCode}
						<IconCaretDownFill />
					{:else}
						<IconCaretRightFill />
					{/if}
				</span>
			{/snippet}
		</Link>

		{#if customCode}
			<p style="color:var(--text-light);font-size:14px;">
				<T key="console.settings.tags.customCodeNote" params={{ strong: { element: 'strong' } }} />
			</p>
		{/if}
	</div>

	{#if customCode}
		<div class="custom-code-input">
			<SplitControl label={i18n.t('console.settings.code.headCode')} column>
				<CodemirrorEditor
					id="tag-head-code"
					ext="twig"
					bind:value={codeHead}
					style="min-height: 200px;"
				/>
			</SplitControl>

			<SplitControl label={i18n.t('console.settings.code.footCode')} column>
				<CodemirrorEditor
					id="tag-head-code"
					ext="twig"
					bind:value={codeHead}
					style="min-height: 200px;"
				/>
			</SplitControl>
		</div>
	{/if}

	{#snippet footer()}
		<Button variant="invisible" on:click={() => (show = false)}
			>{i18n.t('console.common.cancel')}</Button
		>

		<Button on:click={handleUpdate} disabled={!hasChanges || isUpdating}
			>{i18n.t('console.common.update')}</Button
		>
	{/snippet}
</Modal>

<style>
	.custom-code-wrap {
		margin-top: 20px;
		margin-bottom: 10px;
		padding: 0 15px;
	}

	.custom-code-input :global(.CodeMirror) {
		min-height: 200px;
	}
</style>
