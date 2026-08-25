<script lang="ts">
	import {
		Button,
		ButtonGroup,
		Checkbox,
		FormControl,
		InputGroup,
		Modal,
		Radio,
		SplitControl,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import { createWebhook, updateWebhook } from './webhookActions';
	import { type Webhook, WebhookEventType } from '../../../../lib/types';
	import { isValidUrl } from '../../../../lib/helper/is-valid-url';

	interface Props {
		show: boolean;
		webhook?: undefined | Webhook;
		onCreate?: (webhook: Webhook) => void;
		onUpdate?: (webhook: Webhook) => void;
	}

	let { show = $bindable(), webhook = undefined, onCreate, onUpdate }: Props = $props();

	let url = $state(webhook?.url || '');
	let urlError: null | string = $state(null);

	let events: WebhookEventType[] = $state(webhook?.events || []);
	let eventsError: null | string = $state(null);

	let isButtonDisabled = $derived(
		webhook
			? url === webhook.url && JSON.stringify(events) === JSON.stringify(webhook.events)
			: url.trim().length === 0
	);

	function handleChangeEvent(name: WebhookEventType, e: any) {
		if (e.target.checked && !events.includes(name)) {
			events = [...events, name];
		} else {
			events = events.filter((event) => event !== name);
		}
	}

	function isEventChecked(name: string) {
		return events.includes(name as WebhookEventType);
	}

	function selectAllEvents() {
		events = Object.values(WebhookEventType);
	}

	function deselectAllEvents() {
		events = [];
	}

	let allEventsSelected = $derived(
		Object.values(WebhookEventType).every((name) => events.includes(name))
	);
	let noEventsSelected = $derived(events.length === 0);

	let isCreating: boolean | string = $state(false);

	function handleClick() {
		urlError = null;
		eventsError = null;

		if (!url.match(/^https:\/\/.*$/)) {
			urlError = 'URL must start with https://';
			return;
		}

		if (!isValidUrl(url)) {
			urlError = 'Invalid URL';
			return;
		}

		if (events.length === 0) {
			eventsError = 'Please select at least one event';
			return;
		}

		if (webhook) {
			isCreating = 'Updating webhook';

			const updates: Partial<Webhook> = {};

			if (webhook.url !== url) {
				updates.url = url;
			}
			if (JSON.stringify(webhook.events) !== JSON.stringify(events)) {
				updates.events = events;
			}

			updateWebhook(webhook.id, updates)
				.then((res) => {
					toast.success('Webhook updated successfully');
					onUpdate?.(res);
					show = false;
				})
				.catch((err) => {
					toast.error(err.message);
				})
				.finally(() => {
					isCreating = false;
				});
		} else {
			isCreating = 'Creating webhook';

			createWebhook(url, events)
				.then((res) => {
					toast.success('Webhook created successfully');
					onCreate?.(res);
					show = false;
				})
				.catch((err) => {
					toast.error(err.message);
				})
				.finally(() => {
					isCreating = false;
				});
		}
	}
</script>

<Modal
	id="webhook-modal"
	title={webhook ? 'Edit Webhook' : 'Create Webhook'}
	bind:show
	loading={isCreating}
	footer={{
		confirm: {
			text: webhook ? 'Update' : 'Create',
			props: {
				disabled: isButtonDisabled
			}
		}
	}}
	on:confirm={handleClick}
>
	<SplitControl label="Webhook URL" caption="Must be a valid HTTPS URL">
		<FormControl>
			<TextInput
				block
				placeholder="https://example.com/webhook"
				autofocus
				bind:value={url}
				state={urlError ? 'error' : undefined}
			/>

			{#if urlError}
				<Validation state="error">{urlError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl label="Events" caption="Select the events you want to receive">
		<FormControl>
			<div class="events-grid">
				<InputGroup>
					{#each Object.values(WebhookEventType) as name}
						<Checkbox
							value={name}
							checked={isEventChecked(name)}
							on:change={(e) => {
								// @ts-ignore
								handleChangeEvent(name, e);
							}}
						>
							{name}
						</Checkbox>
					{/each}
				</InputGroup>
			</div>

			<div class="events-actions">
				<Button size="small" color="input" disabled={allEventsSelected} on:click={selectAllEvents}>
					Select all
				</Button>
				<Button size="small" color="input" disabled={noEventsSelected} on:click={deselectAllEvents}>
					Deselect all
				</Button>
			</div>

			{#if eventsError}
				<Validation state="error">{eventsError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>
</Modal>

<style>
	.events-actions {
		display: flex;
		gap: 8px;
		margin-top: 12px;
	}

	.events-grid :global(.checkbox-group) {
		display: grid;
		grid-template-columns: 1fr 1fr;
		row-gap: 8px;
		column-gap: 48px !important;
	}

	:global(#webhook-modal-desc) {
		max-height: 60vh;
		overflow-y: auto;
	}
</style>
