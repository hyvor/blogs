<script lang="ts">
	import { Modal, TextInput, FormControl, Validation, toast } from '@hyvor/design/components';
	import { updateMedia } from './mediaActions';
	import { createEventDispatcher } from 'svelte';
	import type { Media } from '../../../lib/types';
	import { toKebabCase } from './mediaUtils';

	let loading = false;

	const dispatch = createEventDispatcher();

	async function handleChangeName() {
		loading = true;
		updateMedia(media.id, { name })
			.then((res) => {
				toast.success('File name updated');
				dispatch('update', res);
				show = false;
			})
			.catch((err) => toast.error(err.message))
			.finally(() => {
				loading = false;
			});
	}

	export let show: boolean;
	export let media: Media;

	let name = media.name;

	const startExt = getExtension(name);
	$: ext = getExtension(name);

	$: kebabName = toKebabCase(name);

	function getExtension(n: string) {
		const parts = n.split('.');
		return (parts[parts.length - 1] || '').trim();
	}
</script>

<div>
	<Modal
		bind:show
		closeOnEscape={false}
		title="Update File Name"
		footer={{
			cancel: {
				text: 'Cancel'
			},
			confirm: {
				text: 'Update'
			}
		}}
		on:cancel={() => (show = false)}
		on:confirm={handleChangeName}
		{loading}
	>
		<FormControl>
			<TextInput
				bind:value={name}
				autofocus
				label="File Name"
				on:keyup={(e) => {
					if (e.key === 'Enter') {
						handleChangeName();
					}
				}}
			/>
			{#if ext !== startExt}
				<Validation state="warning">
					You are changing the file extension from <strong>{startExt}</strong> to
					<strong>{ext}</strong>. Are you sure?
				</Validation>
			{/if}
			{#if name !== kebabName}
				<Validation state="warning">
					The name will be saved as <strong>{kebabName}</strong>.
				</Validation>
			{/if}
		</FormControl>
	</Modal>
</div>
