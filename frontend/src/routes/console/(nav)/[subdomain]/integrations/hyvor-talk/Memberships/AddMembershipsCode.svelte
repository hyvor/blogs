<script lang="ts">
	import {
		Button,
		ButtonGroup,
		Callout,
		CodeBlock,
		Modal,
		toast,
		Validation
	} from '@hyvor/design/components';
	import { blogStore } from '../../../../../lib/stores/blogStore';
	import { updateBlog } from '../../../../../lib/actions/blogActions';
	import { onMount } from 'svelte';
	import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';

	interface Props {
		open?: boolean;
		code: string;
	}

	let { open = $bindable(false), code }: Props = $props();

	function handleUpdate() {
		open = false;

		const toastId = toast.loading('Updating foot code...');

		const codeFoot = $blogStore.code_foot ? $blogStore.code_foot + '\n\n' + code : code;

		updateBlog({ code_foot: codeFoot })
			.then(() => {
				toast.success('Head code updated successfully', { id: toastId });
				open = false;
			})
			.catch(() => {
				toast.error('Failed to update foot code', { id: toastId });
			});
	}

	onMount(() => {
		if (!$blogStore.code_foot) {
			handleUpdate();
		}
	});
</script>

{#if open}
	<Modal title="Update Comments Embed Code" bind:show={open}>
		<p>Your current "Foot Code" is:</p>

		<div class="code-block-wrap">
			<CodeBlock code={$blogStore.code_foot || ''} />
		</div>

		<p>Please confirm that you want to append the memberships code to the foot code.</p>

		{#if ($blogStore.code_foot || '').includes('<hyvor-talk-memberships')}
			<Callout type="warning">
				{#snippet icon()}
					<IconExclamationCircle />
				{/snippet}
				It seems that you already have the memberships code added.
			</Callout>
		{/if}

		{#snippet footer()}
			<ButtonGroup>
				<Button variant="invisible" on:click={() => (open = false)}>Cancel</Button>
				<Button on:click={handleUpdate}>Update</Button>
			</ButtonGroup>
		{/snippet}
	</Modal>
{/if}

<style>
	.code-block-wrap :global(pre) {
		max-height: 300px;
		overflow: auto;
	}
</style>
