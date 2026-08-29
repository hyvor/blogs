<script lang="ts">
	import {
		Button,
		FormControl,
		Label,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import IconSendFill from '@hyvor/icons/IconSendFill';
	import { postSidebarStore, postVariantStore } from '../../../postStore';
	import dayjs from 'dayjs';
	import { updatePost, publishPostVariant } from '../../../postActions';
	import PublishedToast from './Toast/PublishedToast.svelte';
	import PublishSummary from './PublishSummary.svelte';

	let modalOpen = $state(false);
	let type: 'published' | 'scheduled' = $state('published');
	let hasErrors = $state(false);

	let scheduleDate = $state(dayjs().format('YYYY-MM-DD'));

	function openSettings() {
		modalOpen = false;
		$postSidebarStore = 'settings';
	}

	async function handlePublish() {
		modalOpen = false;

		const toastId = toast.loading('Publishing...');

		try {
			await updatePost({
				published_at: type === 'published' ? dayjs().unix() : dayjs(scheduleDate).unix()
			});
		} catch (error) {
			toast.error('Failed to publish post (update post)', { id: toastId });
			return;
		}

		publishPostVariant()
			.then(() => {
				toast.success(PublishedToast, { id: toastId, duration: 5000 });
			})
			.catch(() => {
				toast.error('Failed to publish post', { id: toastId });
			});
	}
</script>

{#if $postVariantStore.status === 'draft'}
	<Button color="accent" on:click={() => (modalOpen = true)} size="small">
		Publish
		{#snippet end()}
			<IconSendFill size={12} />
		{/snippet}
	</Button>

	<Modal title="Publish Post" bind:show={modalOpen} size="large">
		<div class="modal-inner">
			<PublishSummary
				onEditSettings={openSettings}
				onIssues={(issues) => (hasErrors = issues.hasErrors)}
			/>

			<SplitControl label="When">
				<div style="display:flex;gap:10px;flex-direction: column;">
					<Radio name="radio" value="published" bind:group={type}>Publish now</Radio>
					<Radio name="radio" value="scheduled" bind:group={type}>Schedule for later</Radio>
				</div>

				{#if type === 'scheduled'}
					<FormControl style="margin-top:25px;">
						<Label for="publish-time">Schedule Time</Label>
						<TextInput type="date" id="publish-time" bind:value={scheduleDate} />
					</FormControl>
				{/if}
			</SplitControl>
		</div>

		{#snippet footer()}
			<div>
				<Button variant="invisible" on:click={() => (modalOpen = false)}>Cancel</Button>
				<Button color="accent" disabled={hasErrors} on:click={handlePublish}>
					{type === 'published' ? 'Publish' : 'Schedule'}
				</Button>
			</div>
		{/snippet}
	</Modal>
{/if}
