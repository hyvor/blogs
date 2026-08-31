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
	import Newsletter from './Newsletter/Newsletter.svelte';
	import Comments from './Comments/Comments.svelte';
	import Memberships from './Memberships/Memberships.svelte';
	import GatedContentRules from './GatedContentRules/GatedContentRules.svelte';
	import LicenseRequired from '../../../billing/LicenseRequired.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	let isLoading = $state(true);
	let data: HyvorTalkIntegrationData | undefined = $state();

	let isConnecting = $state(false);
	let isDisconnecting = $state(false);

	function handleConnect() {
		isConnecting = false;
		const toastId = toast.loading(i18n.t('console.integrations.hyvorTalk.connecting'));

		createHyvorTalkIntegration()
			.then((res) => {
				data = {
					connected: true,
					data: res
				} as HyvorTalkIntegrationData;
				toast.success(i18n.t('console.integrations.hyvorTalk.connected'), { id: toastId });
			})
			.catch((_) =>
				toast.error(i18n.t('console.integrations.hyvorTalk.failedToConnect'), { id: toastId })
			);
	}

	function handleDisconnect() {
		isDisconnecting = false;
		const toastId = toast.loading(i18n.t('console.integrations.hyvorTalk.disconnecting'));

		deleteHyvorTalkIntegration()
			.then((_) => {
				data = {
					connected: false,
					data: undefined
				};
				toast.success(i18n.t('console.integrations.hyvorTalk.disconnected'), { id: toastId });
			})
			.catch((_) =>
				toast.error(i18n.t('console.integrations.hyvorTalk.failedToDisconnect'), { id: toastId })
			);
	}

	onMount(() => {
		loadHyvorTalk()
			.then((res) => (data = res))
			.catch((_) => toast.error(i18n.t('console.integrations.hyvorTalk.failedToLoad')))
			.finally(() => (isLoading = false));
	});
</script>

<LicenseRequired excludeTrial={true}>
	{#snippet upgradeText()}
		<div>
			<T
				key="console.integrations.hyvorTalk.upgradeText"
				params={{
					link: {
						element: 'a',
						props: {
							href: 'https://talk.hyvor.com',
							target: '_blank',
							style: 'text-decoration:underline'
						}
					}
				}}
			/>
		</div>
	{/snippet}

	{#if isLoading}
		<Loader full />
	{:else if data}
		<SplitControl label={i18n.t('console.integrations.hyvorTalk.introduction')}>
			<div>
				<T
					key="console.integrations.hyvorTalk.introText"
					params={{
						link: {
							element: 'a',
							props: { href: 'https://talk.hyvor.com', target: '_blank', class: 'hds-link' }
						}
					}}
				/>

				<p>
					{i18n.t('console.integrations.hyvorTalk.introNote')}
				</p>
			</div>
		</SplitControl>

		<SplitControl label={i18n.t('console.integrations.hyvorTalk.connectTitle')}>
			{#if data.connected}
				<div class="connection-status">
					<T
						key="console.integrations.hyvorTalk.connectedTo"
						params={{
							strong: { element: 'strong' },
							websiteId: (data as HyvorTalkIntegrationData<true>).data.website_id
						}}
					/>
				</div>

				<Button
					as="a"
					href={`https://talk.hyvor.com/console/${(data as HyvorTalkIntegrationData<true>).data.website_id}/comments`}
					target="_blank"
					size="small"
					style="margin-right:6px;"
				>
					{i18n.t('console.integrations.hyvorTalk.goToConsole')}
				</Button>

				<Button color="red" size="small" on:click={() => (isDisconnecting = true)}>
					{i18n.t('console.integrations.hyvorTalk.disconnect')}
				</Button>
			{:else}
				<div class="connection-status">
					{i18n.t('console.integrations.hyvorTalk.notConnected')}
				</div>

				<Button on:click={() => (isConnecting = true)}>
					{i18n.t('console.integrations.hyvorTalk.connectNow')}
				</Button>
			{/if}
		</SplitControl>

		{#if data.connected}
			<div class="embed-code">
				<SplitControl label={i18n.t('console.integrations.hyvorTalk.embedCodes')}>
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

{#if isConnecting}
	<Modal title={i18n.t('console.integrations.hyvorTalk.connectTitle')} bind:show={isConnecting}>
		<div>
			<p>{i18n.t('console.integrations.hyvorTalk.connectModal.intro')}</p>
			<ul>
				<li>{i18n.t('console.integrations.hyvorTalk.connectModal.point1')}</li>
				<li>
					<T
						key="console.integrations.hyvorTalk.connectModal.point2"
						params={{ b: { element: 'b' } }}
					/>
				</li>
				<li>{i18n.t('console.integrations.hyvorTalk.connectModal.point3')}</li>
				<li>{i18n.t('console.integrations.hyvorTalk.connectModal.point4')}</li>
			</ul>
		</div>

		{#snippet footer()}
			<ButtonGroup>
				<Button variant="invisible" on:click={() => (isConnecting = false)}>
					{i18n.t('console.common.cancel')}
				</Button>
				<Button on:click={handleConnect}>{i18n.t('console.integrations.hyvorTalk.confirm')}</Button>
			</ButtonGroup>
		{/snippet}
	</Modal>
{/if}

{#if isDisconnecting}
	<Modal
		title={i18n.t('console.integrations.hyvorTalk.disconnectTitle')}
		bind:show={isDisconnecting}
	>
		<Callout type="warning">
			{i18n.t('console.integrations.hyvorTalk.disconnectWarning')}
		</Callout>

		<p>
			{i18n.t('console.integrations.hyvorTalk.disconnectConfirm')}
		</p>

		{#snippet footer()}
			<ButtonGroup>
				<Button variant="invisible" on:click={() => (isDisconnecting = false)}>
					{i18n.t('console.common.cancel')}
				</Button>
				<Button color="red" on:click={handleDisconnect}>
					{i18n.t('console.integrations.hyvorTalk.disconnect')}
				</Button>
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
