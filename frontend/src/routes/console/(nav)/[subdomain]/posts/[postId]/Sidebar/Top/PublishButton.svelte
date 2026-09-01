<script lang="ts">
	import {
		Button,
		confetti,
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
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

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

				if (type === 'published') {
					confetti();
				}
			})
			.catch(() => {
				toast.error(i18n.t('console.postEditor.publish.failed'));
			})
			.finally(() => {
				publishing = false;
			});
	}
</script>

{#if $postVariantStore.status === 'draft'}
	<Button color="accent" on:click={() => (modalOpen = true)} size="small">
		{i18n.t('console.postEditor.publish.button')}
		{#snippet end()}
			<IconSendFill size={12} />
		{/snippet}
	</Button>
{/if}

<Modal
	title={i18n.t('console.postEditor.publish.modalTitle')}
	bind:show={modalOpen}
	size={published ? 'small' : 'large'}
	loading={publishing ? i18n.t('console.postEditor.publish.publishing') : false}
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

		<SplitControl label={i18n.t('console.postEditor.publish.when')}>
			<div style="display:flex;gap:10px;flex-direction: column;">
				<Radio name="radio" value="published" bind:group={type}
					>{i18n.t('console.postEditor.publish.publishNow')}</Radio
				>
				<Radio name="radio" value="scheduled" bind:group={type}
					>{i18n.t('console.postEditor.publish.scheduleForLater')}</Radio
				>
			</div>
		</SplitControl>

		{#if type === 'scheduled'}
			<div transition:slide>
				<SplitControl
					label={i18n.t('console.postEditor.publish.scheduleTime')}
					caption={i18n.t('console.postEditor.publish.scheduleTimezone', {
						timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
					})}
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
							<Validation state="error"
								>{i18n.t('console.postEditor.publish.schedulePast')}</Validation
							>
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
				{published.status === 'scheduled'
					? i18n.t('console.postEditor.publish.successScheduled')
					: i18n.t('console.postEditor.publish.successPublished')}
			</div>
			{#if published.status === 'published'}
				<div class="button">
					<Button as="a" href={published.url} target="_blank" color="accent">
						{i18n.t('console.postEditor.publish.viewPost')}
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
				<Button variant="invisible" on:click={() => (modalOpen = false)}
					>{i18n.t('console.common.cancel')}</Button
				>
				<Button color="accent" disabled={hasErrors || isPastSchedule} on:click={handlePublish}>
					{type === 'published'
						? i18n.t('console.postEditor.publish.button')
						: i18n.t('console.postEditor.publish.schedule')}
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
