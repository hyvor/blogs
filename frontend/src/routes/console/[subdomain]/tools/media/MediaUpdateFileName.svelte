<script lang="ts">
	import { Modal, TextInput, FormControl, toast } from '@hyvor/design/components';
	import { updateMedia } from './mediaActions';
	import { createEventDispatcher } from 'svelte';
	import type { Media } from '../../../lib/types';

	let loading = false;

	const dispatch = createEventDispatcher();

	async function handleChangeName() {
		updateMedia(media.id, { name: media.name })
			.then(() => {
				toast.success('File name updated');
				dispatch('update', media);
			})
			.catch((err) => toast.error(err.message))
			.finally(() => {
				show = false;
				loading = false;
			});
	}

	export let show: boolean;
	export let media: Media;
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
			<TextInput bind:value={media.name} label="File Name" />
		</FormControl>
	</Modal>
</div>
