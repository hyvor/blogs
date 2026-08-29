<script lang="ts">
	import {
		Button,
		FormControl,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		toast,
		Validation
	} from '@hyvor/design/components';
	import IconSendFill from '@hyvor/icons/IconSendFill';
	import { postSidebarStore, postVariantStore } from '../../../postStore';
	import dayjs from 'dayjs';
	import utc from 'dayjs/plugin/utc';
	import { publishPostVariant } from '../../../postActions';
	import PublishedToast from './Toast/PublishedToast.svelte';
	import PublishSummary from './PublishSummary.svelte';
	import { slide } from 'svelte/transition';

	dayjs.extend(utc);

	let modalOpen = $state(false);
	let type: 'published' | 'scheduled' = $state('published');
	let hasErrors = $state(false);

	let scheduleDateTime = $state(dayjs.utc().add(1, 'hour').format('YYYY-MM-DDTHH:mm'));

	function checkIsPastSchedule() {
		return type === 'scheduled' && dayjs.utc(scheduleDateTime).isBefore(dayjs.utc());
	}

	let isPastSchedule = $derived(checkIsPastSchedule());

	function openSettings() {
		modalOpen = false;
		$postSidebarStore = 'settings';
	}

	function handlePublish() {
		modalOpen = false;

		const toastId = toast.loading('Publishing...');

		const publishAt = type === 'scheduled' ? dayjs.utc(scheduleDateTime).unix() : null;

		publishPostVariant(publishAt)
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
			</SplitControl>

			{#if type === 'scheduled'}
				<div transition:slide>
					<SplitControl label="Schedule Time" caption="Time is in UTC">
						<FormControl>
							<TextInput
								type="datetime-local"
								id="publish-time"
								min={dayjs.utc().format('YYYY-MM-DDTHH:mm')}
								bind:value={scheduleDateTime}
								state={isPastSchedule ? 'error' : 'default'}
							/>
							{#if isPastSchedule}
								<Validation state="error">Schedule time cannot be in the past.</Validation>
							{/if}
						</FormControl>
					</SplitControl>
				</div>
			{/if}
		</div>

		{#snippet footer()}
			<div>
				<Button variant="invisible" on:click={() => (modalOpen = false)}>Cancel</Button>
				<Button color="accent" disabled={hasErrors || isPastSchedule} on:click={handlePublish}>
					{type === 'published' ? 'Publish' : 'Schedule'}
				</Button>
			</div>
		{/snippet}
	</Modal>
{/if}
