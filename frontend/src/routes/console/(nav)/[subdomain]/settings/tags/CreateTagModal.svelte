<script lang="ts">
	import {
		Button,
		ButtonGroup,
		Caption,
		FormControl,
		Link,
		Modal,
		SplitControl,
		Switch,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import { createTag } from './tagActions';
	import { createEventDispatcher } from 'svelte';

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	let name: string = $state('');
	let isPrivate = $state(false);

	const dispatch = createEventDispatcher();

	function handleClick() {
		const toastId = toast.loading('Creating tag...');

		show = false;

		createTag(name, isPrivate)
			.then((res) => {
				toast.success('Tag created.', { id: toastId });
				dispatch('create', res);
			})
			.catch((err) => {
				toast.error(err.message, { id: toastId });
			});
	}

	let isButtonDisabled = $derived(name.trim().length === 0);
</script>

<Modal title="Create Tag" bind:show>
	<SplitControl label="Name" caption="The name of the tag">
		<FormControl>
			<TextInput bind:value={name} placeholder="Blogging" block autofocus />
		</FormControl>
	</SplitControl>

	<SplitControl label="Private">
		{#snippet caption()}
			<Caption>
				<Link href="/docs/tags#private" target="_blank">Private tags</Link> are not visible on
				public pages - only for internal use.
			</Caption>
		{/snippet}
		<Switch bind:checked={isPrivate} />
	</SplitControl>

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (show = false)}>Cancel</Button>

			<Button on:click={handleClick} disabled={isButtonDisabled}>Create</Button>
		</ButtonGroup>
	{/snippet}
</Modal>
