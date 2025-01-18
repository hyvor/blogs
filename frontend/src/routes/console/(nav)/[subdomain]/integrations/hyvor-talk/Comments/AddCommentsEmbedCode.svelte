<script lang="ts">
	import { Button, ButtonGroup, CodeBlock, Modal, toast } from '@hyvor/design/components';
	import { blogStore } from '../../../../../lib/stores/blogStore';
	import { updateBlog } from '../../../../../lib/actions/blogActions';
	import { onMount } from 'svelte';

	export let open = false;
	export let code: string;

	function handleUpdate() {
		open = false;

		const toastId = toast.loading('Updating comments embed code...');

		updateBlog({ comments_code: code })
			.then(() => {
				toast.success('Comments embed code updated successfully', { id: toastId });
				open = false;
			})
			.catch(() => {
				toast.error('Failed to update comments embed code', { id: toastId });
			});
	}

	onMount(() => {
		if (!$blogStore.comments_code) {
			handleUpdate();
		}
	});
</script>

{#if open}
	<Modal title="Update Comments Embed Code" bind:show={open}>
		<p>Your current "Comments Embed Code" is:</p>

		<div class="code-block-wrap">
			<CodeBlock code={$blogStore.comments_code || ''} />
		</div>

		<p>Please confirm that you want to update it to Hyvor Talk embed code.</p>

		<svelte:fragment slot="footer">
			<ButtonGroup>
				<Button variant="invisible" on:click={() => (open = false)}>Cancel</Button>
				<Button on:click={handleUpdate}>Update</Button>
			</ButtonGroup>
		</svelte:fragment>
	</Modal>
{/if}

<style>
	.code-block-wrap :global(pre) {
		max-height: 300px;
		overflow: auto;
	}
</style>
