<script lang="ts">
	import { onMount } from 'svelte';
	import { Callout, toast } from '@hyvor/design/components';
	import { getConfig } from '../lib/config';
	import { hasPaymentMethodStore } from '../lib/stores';

	interface Props {
		canSubmit?: boolean;
		busy?: boolean;
	}

	let { canSubmit = $bindable(false), busy = $bindable(false) }: Props = $props();

	let wrap: HTMLDivElement | undefined = $state();
	let pending: ((success: boolean) => void) | null = null;

	function settle(success: boolean) {
		pending?.(success);
		pending = null;
	}

	/**
	 * Asks the card iframe to submit (confirm) the card.
	 * Resolves to true when the card is saved, false otherwise
	 */
	export function submit(): Promise<boolean> {
		return new Promise((resolve) => {
			pending = resolve;
			window.dispatchEvent(new CustomEvent('card-component-iframe-submit'));
		});
	}

	function added() {
		// updates the UI immediately, no reload needed
		hasPaymentMethodStore.set(true);
		toast.success('Card confirmed. You can now create your blog.');
		settle(true);
	}

	onMount(() => {
		if (!wrap) {
			return;
		}

		const onSuccess = () => added();
		// the org already has a card (e.g., added in another tab)
		const onExisting = () => hasPaymentMethodStore.set(true);
		const onError = (e: Event) => {
			const message = (e as CustomEvent).detail?.message;
			toast.error(message || 'Failed to add the card. Please try again.');
			settle(false);
		};
		const onState = (e: Event) => {
			const detail = (e as CustomEvent).detail;
			canSubmit = !!detail?.canSubmit;
			busy = !!detail?.busy;
			// the iframe shows validation errors itself, so stop waiting once it is idle
			if (!busy && pending) {
				// success/error events arrive right after; give them a moment
				setTimeout(() => settle(false), 500);
			}
		};

		window.addEventListener('card-component-iframe-success', onSuccess);
		window.addEventListener('card-component-iframe-existing', onExisting);
		window.addEventListener('card-component-iframe-error', onError);
		window.addEventListener('card-component-iframe-state', onState);

		const script = document.createElement('script');
		script.src = getConfig().hyvor.instance + '/js/card-component-iframe.js';
		// the "Confirm Card" button is in the parent (ResourceCreator)
		script.setAttribute('data-external', '');
		wrap.appendChild(script);

		return () => {
			window.removeEventListener('card-component-iframe-success', onSuccess);
			window.removeEventListener('card-component-iframe-existing', onExisting);
			window.removeEventListener('card-component-iframe-error', onError);
			window.removeEventListener('card-component-iframe-state', onState);
			script.remove();
			wrap?.querySelector('iframe')?.remove();
		};
	});
</script>

<div class="card-required">
	<Callout type="info">
		We ask for a card to keep spam off our platform.
		<strong>You won't be charged</strong> unless you choose to upgrade later.
	</Callout>

	<div class="wrap" bind:this={wrap}></div>
</div>

<style>
	.wrap {
		margin-top: 15px;
	}
</style>
