<script lang="ts">
	import { Modal, TextInput, FormControl, toast } from '@hyvor/design/components';
	import { updateMedia } from './mediaActions';
	import { createEventDispatcher } from 'svelte';
	import type { Media } from '../../../lib/types';

	let loading = false;

	const dispatch = createEventDispatcher();

	async function handleChangeName() {
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
	>
		<FormControl>
			<TextInput bind:value={name} label="File Name" />
		</FormControl>
	</Modal>
</div>
