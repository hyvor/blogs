<script lang="ts">
	import {
		Button,
		ButtonGroup,
		Callout,
		Loader,
		Modal,
		SplitControl,
		toast
	} from '@hyvor/design/components';
	import {
		loadHyvorTalk,
		type HyvorTalkIntegrationData,
		createHyvorTalkIntegration,
		deleteHyvorTalkIntegration
	} from './hyvorTalkActions';
	import { onMount } from 'svelte';
	import Comments from './Comments/Comments.svelte';
	import LicenseRequired from '../../../billing/LicenseRequired.svelte';
	import IntergrationTopNotice from '../components/IntergrationTopNotice.svelte';
	import { getConfig } from '../../../../lib/config';
	import IntegrationNotAvailable from '../components/IntegrationNotAvailable.svelte';
	import { consoleUrlWithBlog } from '../../../../lib/consoleUrl';

	let isLoading = $state(true);
	let data: HyvorTalkIntegrationData | undefined = $state();

	let isConnecting = $state(false);
	let isDisconnecting = $state(false);

	function handleConnect() {
		isConnecting = false;
		const toastId = toast.loading('Connecting to Hyvor Talk...');

		createHyvorTalkIntegration()
			.then((res) => {
				data = {
					connected: true,
					data: res
				} as HyvorTalkIntegrationData;
				toast.success('Hyvor Talk connected successfully', { id: toastId });
			})
			.catch((_) => toast.error('Failed to connect to Hyvor Talk', { id: toastId }));
	}

	function handleDisconnect() {
		isDisconnecting = false;
		const toastId = toast.loading('Disconnecting from Hyvor Talk...');

		deleteHyvorTalkIntegration()
			.then((_) => {
				data = {
					connected: false,
					data: undefined
				};
				toast.success('Hyvor Talk disconnected successfully', { id: toastId });
			})
			.catch((_) => toast.error('Failed to disconnect from Hyvor Talk', { id: toastId }));
	}

	onMount(() => {
		loadHyvorTalk()
			.then((res) => (data = res))
			.catch((_) => toast.error('Failed to load Hyvor Talk integration data'))
			.finally(() => (isLoading = false));
	});
</script>

{#if getConfig().deployment === 'on-prem'}
	<IntegrationNotAvailable>
		Hyvor Talk integration is not available in self-hosted deployments. However, you can easily
		embed Hyvor Talk or another commenting system by adding the embed code directly in <a
			href={consoleUrlWithBlog('/settings/comments')}
			class="hds-link">Settings &rarr; Comments & Newsletters</a
		>.
	</IntegrationNotAvailable>
{:else}
	<IntergrationTopNotice>
		<a href="https://talk.hyvor.com" target="_blank" class="hds-link"> Hyvor Talk </a> is a privacy-first
		commenting platform. All Hyvor Blogs plans include a free complimentary license for Hyvor Talk.
	</IntergrationTopNotice>

	<LicenseRequired excludeTrial={true}>
		{#snippet upgradeText()}
			<div>
				This integration allows you to use <a
					href="https://talk.hyvor.com"
					target="_blank"
					style="text-decoration:underline">Hyvor Talk</a
				> on your blog for FREE. Upgrade to any plan to use this integration. This integration is not
				available in the trial period.
			</div>
		{/snippet}

		{#if isLoading}
			<Loader full />
		{:else if data}
			<SplitControl label="Connect Hyvor Talk">
				{#if data.connected}
					<div class="connection-status">
						This blog is connected to website ID <strong
							>{(data as HyvorTalkIntegrationData<true>).data.website_id}</strong
						> in Hyvor Talk. Visit the Hyvor Talk Console to manage comments and memberships.
					</div>

					<Button
						as="a"
						href={`https://talk.hyvor.com/console/${(data as HyvorTalkIntegrationData<true>).data.website_id}/comments`}
						target="_blank"
						size="small"
						style="margin-right:6px;"
					>
						Go to Hyvor Talk Console
					</Button>

					<Button color="red" size="small" on:click={() => (isDisconnecting = true)}
						>Disconnect</Button
					>
				{:else}
					<div class="connection-status">
						This blog is not connected to a website in Hyvor Talk.
					</div>

					<Button on:click={() => (isConnecting = true)}>Connect Now</Button>
				{/if}
			</SplitControl>

			{#if data.connected}
				<div class="embed-code">
					<SplitControl label="Embed Codes">
						{#snippet nested()}
							<div>
								<Comments websiteId={(data as HyvorTalkIntegrationData<true>).data.website_id} />
								<Newsletter websiteId={(data as HyvorTalkIntegrationData<true>).data.website_id} />
								<Memberships websiteId={(data as HyvorTalkIntegrationData<true>).data.website_id} />
							</div>
						{/snippet}
					</SplitControl>
				</div>
				<GatedContentRules />
			{/if}
		{/if}
	</LicenseRequired>
{/if}

{#if isConnecting}
	<Modal title="Connect Hyvor Talk" bind:show={isConnecting}>
		<div>
			<p>Please confirm that you want to create a website ID in Hyvor Talk for this blog.</p>
			<ul>
				<li>A new Hyvor Talk website ID will be created under your HYVOR account.</li>
				<li>This new website can <b>only</b> be used on this blog.</li>
				<li>It is free of charge.</li>
				<li>
					If you have any other websites on Hyvor Talk, you will need a separate subscription.
				</li>
			</ul>
		</div>

		{#snippet footer()}
			<ButtonGroup>
				<Button variant="invisible" on:click={() => (isConnecting = false)}>Cancel</Button>
				<Button on:click={handleConnect}>Confirm</Button>
			</ButtonGroup>
		{/snippet}
	</Modal>
{/if}

{#if isDisconnecting}
	<Modal title="Disconnect Hyvor Talk" bind:show={isDisconnecting}>
		<Callout type="warning">You cannot connect this blog to the same website ID again.</Callout>

		<p>
			Are you sure you want to disconnect this blog from Hyvor Talk? This will not delete your Hyvor
			Talk Website ID. You will have to delete it manually from the Hyvor Talk Console.
		</p>

		{#snippet footer()}
			<ButtonGroup>
				<Button variant="invisible" on:click={() => (isDisconnecting = false)}>Cancel</Button>
				<Button color="red" on:click={handleDisconnect}>Disconnect</Button>
			</ButtonGroup>
		{/snippet}
	</Modal>
{/if}

<style>
	.connection-status {
		margin-bottom: 10px;
	}

	.embed-code :global(.split-control .right) {
		min-width: 0;
	}

	.embed-code {
		margin-bottom: 20px;
	}
</style>
