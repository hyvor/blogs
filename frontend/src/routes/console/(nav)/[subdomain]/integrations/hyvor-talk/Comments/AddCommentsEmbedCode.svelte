<script lang="ts">
	import { Button, ButtonGroup, CodeBlock, Modal, toast } from '@hyvor/design/components';
	import { blogStore } from '../../../../../lib/stores/blogStore';
	import { updateBlog } from '../../../../../lib/actions/blogActions';
	import { onMount } from 'svelte';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		open?: boolean;
		code: string;
	}

	let { open = $bindable(false), code }: Props = $props();

	function handleUpdate() {
		open = false;

		const toastId = toast.loading(i18n.t('console.integrations.hyvorTalk.comments.updating'));

		updateBlog({ comments_code: code })
			.then(() => {
				toast.success(i18n.t('console.integrations.hyvorTalk.comments.updated'), { id: toastId });
				open = false;
			})
			.catch(() => {
				toast.error(i18n.t('console.integrations.hyvorTalk.comments.failedToUpdate'), {
					id: toastId
				});
			});
	}

	onMount(() => {
		if (!$blogStore.comments_code) {
			handleUpdate();
		}
	});
</script>

{#if open}
	<Modal title={i18n.t('console.integrations.hyvorTalk.comments.updateTitle')} bind:show={open}>
		<p>{i18n.t('console.integrations.hyvorTalk.comments.currentCode')}</p>

		<div class="code-block-wrap">
			<CodeBlock code={$blogStore.comments_code || ''} />
		</div>

		<p>{i18n.t('console.integrations.hyvorTalk.comments.confirmUpdate')}</p>

		{#snippet footer()}
			<ButtonGroup>
				<Button variant="invisible" on:click={() => (open = false)}>
					{i18n.t('console.common.cancel')}
				</Button>
				<Button on:click={handleUpdate}>{i18n.t('console.common.update')}</Button>
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
