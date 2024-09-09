<script lang="ts">
	import {
		Button,
		ButtonGroup,
		FormControl,
		Link,
		Modal,
		SplitControl,
		Switch,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import type { Tag, TagVariant } from '../../../../lib/types';
	import CodemirrorEditor from '../../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte';
	import { IconCaretDownFill, IconCaretRightFill } from '@hyvor/icons';
	import VariantInput from '../../@components/VariantInput/VariantInput.svelte';
	import { updateTag, updateTagVariant } from '../tagActions';
	import { createEventDispatcher } from 'svelte';
	import TagSlug from './TagSlug.svelte';

	export let show = false;
	export let tag: Tag;

	let isPrivate = tag.is_private;
	let slug = tag.slug;
	let codeHead = tag.code_head || '';
	let codeFoot = tag.code_foot || '';

	let customCode = false;

	const variantChanges: Record<number, Partial<TagVariant>> = {};

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

	$: hasChanges =
		Object.keys(variantChanges).length !== 0 ||
		slug !== tag.slug ||
		codeHead !== (tag.code_head || '') ||
		codeFoot !== (tag.code_foot || '');

	const dispatch = createEventDispatcher();

	let isUpdating = false;

	async function handleUpdate() {
		isUpdating = true;

		const toastId = toast.loading('Updating tag');

		for (const [languageId, changes] of Object.entries(variantChanges)) {
			try {
				await updateTagVariant(tag.id, Number(languageId), changes);
			} catch (e) {
				toast.error('Failed to update tag variant', { id: toastId });
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
				toast.success('Tag updated', { id: toastId });
				show = false;
				dispatch('update', res);
			})
			.catch((e) => {
				toast.error('Failed to update tag', { id: toastId });
			})
			.finally(() => {
				isUpdating = false;
			});
	}
</script>

<Modal title="Edit tag" bind:show>
	<SplitControl label="Private">
		<Switch bind:checked={isPrivate} />
	</SplitControl>

	<VariantInput
		obj={tag}
		type="tag"
		key="name"
		label="Name"
		caption="Name of the tag"
		maxlength={255}
		on:variantCreate
		on:change={handleNameChange}
	/>

	<VariantInput
		obj={tag}
		type="tag"
		key="description"
		label="Description"
		caption="A short description of the tag"
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
			Custom Code

			<span slot="end">
				{#if customCode}
					<IconCaretDownFill />
				{:else}
					<IconCaretRightFill />
				{/if}
			</span>
		</Link>

		{#if customCode}
			<p style="color:var(--text-light);font-size:14px;">
				Custom code is added to <strong>all posts</strong> that have this tag. It is not added to the
				tag page.
			</p>
		{/if}
	</div>

	{#if customCode}
		<div class="custom-code-input">
			<SplitControl label="Head Code" column>
				<CodemirrorEditor
					id="tag-head-code"
					ext="twig"
					bind:value={codeHead}
					style="min-height: 200px;"
				/>
			</SplitControl>

			<SplitControl label="Foot Code" column>
				<CodemirrorEditor
					id="tag-head-code"
					ext="twig"
					bind:value={codeHead}
					style="min-height: 200px;"
				/>
			</SplitControl>
		</div>
	{/if}

	<svelte:fragment slot="footer">
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (show = false)}>Cancel</Button>

			<Button on:click={handleUpdate} disabled={!hasChanges || isUpdating}>Update</Button>
		</ButtonGroup>
	</svelte:fragment>
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
