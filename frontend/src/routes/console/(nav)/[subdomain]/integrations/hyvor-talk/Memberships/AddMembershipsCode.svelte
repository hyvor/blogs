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
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		open?: boolean;
		code: string;
	}

	let { open = $bindable(false), code }: Props = $props();

	function handleUpdate() {
		open = false;

		const toastId = toast.loading(i18n.t('console.integrations.hyvorTalk.memberships.updating'));

		const codeFoot = $blogStore.code_foot ? $blogStore.code_foot + '\n\n' + code : code;

		updateBlog({ code_foot: codeFoot })
			.then(() => {
				toast.success(i18n.t('console.integrations.hyvorTalk.memberships.updated'), {
					id: toastId
				});
				open = false;
			})
			.catch(() => {
				toast.error(i18n.t('console.integrations.hyvorTalk.memberships.failedToUpdate'), {
					id: toastId
				});
			});
	}

	onMount(() => {
		if (!$blogStore.code_foot) {
			handleUpdate();
		}
	});
</script>

{#if open}
	<Modal title={i18n.t('console.integrations.hyvorTalk.memberships.updateTitle')} bind:show={open}>
		<p>{i18n.t('console.integrations.hyvorTalk.memberships.currentCode')}</p>

		<div class="code-block-wrap">
			<CodeBlock code={$blogStore.code_foot || ''} />
		</div>

		<p>{i18n.t('console.integrations.hyvorTalk.memberships.confirmAppend')}</p>

		{#if ($blogStore.code_foot || '').includes('<hyvor-talk-memberships')}
			<Callout type="warning">
				{#snippet icon()}
					<IconExclamationCircle />
				{/snippet}
				{i18n.t('console.integrations.hyvorTalk.memberships.alreadyAdded')}
			</Callout>
		{/if}

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
