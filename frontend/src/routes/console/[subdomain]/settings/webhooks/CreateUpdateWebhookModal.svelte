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
	import { createEventDispatcher } from 'svelte';
	import { createWebhook, updateWebhook } from './webhookActions';
	import { type Webhook, WebhookEventType } from '../../../lib/types';
	import { isValidUrl } from '../../../lib/helper/is-valid-url';

	export let show: boolean;
	export let webhook: undefined | Webhook = undefined;

	let url = webhook?.url || '';
	let urlError: null | string = null;

	let events: WebhookEventType[] = webhook?.events || [];
	let eventsError: null | string = null;

	$: isButtonDisabled = webhook
		? url === webhook.url && JSON.stringify(events) === JSON.stringify(webhook.events)
		: url.trim().length === 0;

	const dispatch = createEventDispatcher();

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

	let isCreating: boolean | string = false;

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
					dispatch('update', res);
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
					dispatch('create', res);
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

			{#if eventsError}
				<Validation state="error">{eventsError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>
</Modal>
