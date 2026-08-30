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
	import { publishPostVariant } from '../../../postActions';
	import PublishSummary from './PublishSummary.svelte';
	import { slide } from 'svelte/transition';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';

	let modalOpen = $state(false);
	let type: 'published' | 'scheduled' = $state('published');
	let hasErrors = $state(false);

	let scheduleDateTime = $state(dayjs().add(1, 'hour').format('YYYY-MM-DDTHH:mm'));

	function checkIsPastSchedule() {
		return type === 'scheduled' && dayjs(scheduleDateTime).isBefore(dayjs());
	}

	let isPastSchedule = $derived(checkIsPastSchedule());

	let publishing = $state(false);
	let published: null | { status: 'published' | 'scheduled'; url: string } = $state(null);

	function openSettings() {
		modalOpen = false;
		$postSidebarStore = 'settings';
	}

	function handlePublish() {
		publishing = true;

		const publishAt = type === 'scheduled' ? dayjs(scheduleDateTime).unix() : null;

		publishPostVariant(publishAt)
			.then((v) => {
				published = { status: type, url: v.url };
			})
			.catch(() => {
				toast.error('Failed to publish post');
			})
			.finally(() => {
				publishing = false;
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
{/if}

<Modal
	title="Publish Post"
	bind:show={modalOpen}
	size={published ? 'small' : 'large'}
	loading={publishing ? 'Publishing...' : false}
	bare={published !== null}
	onclose={() => {
		if (published !== null) {
			published = null;
		}
	}}
>
	{#if published === null}
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
				<SplitControl
					label="Schedule Time"
					caption="Time is in your local timezone ({Intl.DateTimeFormat().resolvedOptions()
						.timeZone})"
				>
					<FormControl>
						<TextInput
							type="datetime-local"
							id="publish-time"
							min={dayjs().format('YYYY-MM-DDTHH:mm')}
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
	{:else}
		<div class="published status-{published.status}">
			<div class="icon">
				<IconCheck size={26} />
			</div>
			<div class="title">
				Post successfully {published.status}!
			</div>
			{#if published.status === 'published'}
				<div class="button">
					<Button as="a" href={published.url} target="_blank" color="accent">
						View Post
						{#snippet end()}
							<IconBoxArrowUpRight size={10} />
						{/snippet}
					</Button>
				</div>
			{/if}
		</div>
	{/if}

	{#snippet footer()}
		{#if published === null}
			<div>
				<Button variant="invisible" on:click={() => (modalOpen = false)}>Cancel</Button>
				<Button color="accent" disabled={hasErrors || isPastSchedule} on:click={handlePublish}>
					{type === 'published' ? 'Publish' : 'Schedule'}
				</Button>
			</div>
		{/if}
	{/snippet}
</Modal>

<style>
	.published {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 15px;
		padding: 25px 0;
	}

	.published .icon {
		width: 35px;
		height: 35px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.published.status-published .icon {
		background-color: var(--green-light);
		color: var(--green-dark);
	}

	.published .title {
		font-size: 16px;
		font-weight: 600;
	}

	.published .button {
		margin-top: 5px;
	}
</style>
