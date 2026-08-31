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
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

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

	let isCreating: boolean | string = $state(false);

	function handleClick() {
		urlError = null;
		eventsError = null;

		if (!url.match(/^https:\/\/.*$/)) {
			urlError = i18n.t('console.settings.webhooks.validation.urlHttps');
			return;
		}

		if (!isValidUrl(url)) {
			urlError = i18n.t('console.settings.webhooks.validation.urlInvalid');
			return;
		}

		if (events.length === 0) {
			eventsError = i18n.t('console.settings.webhooks.validation.eventsRequired');
			return;
		}

		if (webhook) {
			isCreating = i18n.t('console.settings.webhooks.updating');

			const updates: Partial<Webhook> = {};

			if (webhook.url !== url) {
				updates.url = url;
			}
			if (JSON.stringify(webhook.events) !== JSON.stringify(events)) {
				updates.events = events;
			}

			updateWebhook(webhook.id, updates)
				.then((res) => {
					toast.success(i18n.t('console.settings.webhooks.updated'));
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
			isCreating = i18n.t('console.settings.webhooks.creating');

			createWebhook(url, events)
				.then((res) => {
					toast.success(i18n.t('console.settings.webhooks.created'));
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
	title={webhook
		? i18n.t('console.settings.webhooks.editWebhook')
		: i18n.t('console.settings.webhooks.create')}
	bind:show
	loading={isCreating}
	footer={{
		confirm: {
			text: webhook ? i18n.t('console.common.update') : i18n.t('console.common.create'),
			props: {
				disabled: isButtonDisabled
			}
		}
	}}
	on:confirm={handleClick}
>
	<SplitControl
		label={i18n.t('console.settings.webhooks.webhookUrl')}
		caption={i18n.t('console.settings.webhooks.webhookUrlCaption')}
	>
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

	<SplitControl
		label={i18n.t('console.settings.webhooks.events')}
		caption={i18n.t('console.settings.webhooks.eventsCaption')}
	>
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
