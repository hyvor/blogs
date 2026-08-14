<script lang="ts">
	import {
		Button,
		Loader,
		SplitControl,
		Textarea,
		confirm,
		toast,
		Tag
	} from '@hyvor/design/components';
	import {
		loadHyvorTalk,
		connectHyvorTalk,
		disconnectHyvorTalk,
		updateHyvorTalkEmbedCode,
		type HyvorTalkIntegration
	} from './hyvorTalkActions';
	import { onMount } from 'svelte';
	import IntergrationTopNotice from '../components/IntergrationTopNotice.svelte';
	import IntegrationConfigContent from '../components/IntegrationConfigContent.svelte';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import { consoleUrlWithBlog } from '../../../../lib/consoleUrl';
	import { getConfig } from '../../../../lib/config';
	import IntegrationNotAvailable from '../components/IntegrationNotAvailable.svelte';
	import { setHyvorTalkIntegrationState } from '../../../../lib/stores/blogStore';
	import DisconnectConfirm from './DisconnectConfirm.svelte';

	let isLoading = $state(true);
	let data: HyvorTalkIntegration | null = $state(null);
	let embedCode = $state('');

	async function handleConnect() {
		const confirmed = await confirm({
			title: 'Connect Hyvor Talk',
			content:
				'A new website will be created on Hyvor Talk under your organization, and this blog will be connected to it.',
			confirmText: 'Yes, Connect',
			autoClose: false
		});

		if (!confirmed) return;

		confirmed.loading('Creating a website on Hyvor Talk...');

		connectHyvorTalk()
			.then((res) => {
				setFromIntegration(res);
				setHyvorTalkIntegrationState(res.website_id);
				toast.success('Hyvor Talk connected successfully');
			})
			.catch((err) => toast.error(err.message || 'Failed to connect to Hyvor Talk'))
			.finally(() => confirmed.close());
	}

	async function handleDisconnect() {
		const confirmed = await confirm({
			title: 'Disconnect Hyvor Talk & Delete Website',
			content: DisconnectConfirm,
			confirmText: 'Yes, Disconnect & Delete Website',
			danger: true
		});

		if (!confirmed) return;

		const toastId = toast.loading('Disconnecting from Hyvor Talk...');

		disconnectHyvorTalk()
			.then(() => {
				setFromIntegration(null);
				setHyvorTalkIntegrationState(null);
				toast.success('Hyvor Talk disconnected successfully', { id: toastId });
			})
			.catch((err) =>
				toast.error(err.message || 'Failed to disconnect from Hyvor Talk', { id: toastId })
			);
	}

	function handleSaveEmbedCode() {
		const toastId = toast.loading('Saving embed code...');

		updateHyvorTalkEmbedCode(embedCode)
			.then((res) => {
				setFromIntegration(res);
				toast.success('Embed code saved', { id: toastId });
			})
			.catch((err) => toast.error(err.message || 'Failed to save embed code', { id: toastId }));
	}

	function handleResetEmbedCode() {
		embedCode = data!.embed_default_code;
	}

	let isEmbedCodeDirty = $derived.by(() => data && embedCode !== data.embed_code);
	let isEmbedCodeDefault = $derived.by(() => embedCode === data?.embed_default_code);

	onMount(() => {
		loadHyvorTalk()
			.then((res) => {
				setFromIntegration(res.data);
			})
			.catch(() => toast.error('Failed to load Hyvor Talk integration data'))
			.finally(() => (isLoading = false));
	});

	function setFromIntegration(int: HyvorTalkIntegration | null) {
		data = int;
		if (int) {
			embedCode = int.embed_code;
		}
	}

	function handleCopy() {
		navigator.clipboard.writeText(embedCode).then(() => {
			toast.success('Embed code copied to clipboard');
		});
	}
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

	<IntegrationConfigContent>
		{#if isLoading}
			<Loader full />
		{:else}
			<SplitControl label="Hyvor Talk Connection">
				{#if data}
					<div class="connection-status">
						This blog is connected to a website (ID: <strong>{data.website_id}</strong>) in Hyvor
						Talk.
					</div>

					<Button
						as="a"
						href={consoleUrlWithBlog('/comments')}
						size="small"
						style="margin-right:6px;"
					>
						Manage Comments
						{#snippet end()}
							&rarr;
						{/snippet}
					</Button>

					<Button
						as="a"
						href={`${getConfig().hyvor.hyvor_talk_url}/console/${data.website_id}`}
						target="_blank"
						size="small"
						style="margin-right:6px;"
						variant="outline"
					>
						Hyvor Talk Console
						{#snippet end()}
							<IconBoxArrowUpRight size={10} />
						{/snippet}
					</Button>

					<Button color="red" size="small" on:click={handleDisconnect}>Disconnect</Button>
				{:else}
					<div class="connection-status">
						<Tag>Not Connected</Tag>
					</div>

					<Button onclick={handleConnect}>Connect Now</Button>
				{/if}
			</SplitControl>

			{#if data}
				<div class="embed-code">
					<SplitControl label="Embed Code">
						{#snippet caption()}
							<div>
								This code is automatically added to your blog's <a
									href="/docs/themes-templates#placeholders"
									class="hds-link"
									target="_blank"
									><code>_comments</code>
									variable</a
								>, which is usually placed below the post content (depending on the theme).
							</div>
						{/snippet}

						<Textarea bind:value={embedCode} rows={4} block />

						<div class="embed-code-actions">
							<div class="actions-left">
								{#if isEmbedCodeDirty}
									<Button size="small" on:click={handleSaveEmbedCode}>Save</Button>
								{/if}
								{#if !isEmbedCodeDefault}
									<Button size="small" variant="invisible" on:click={handleResetEmbedCode}
										>Reset to default</Button
									>
								{/if}
							</div>
							<Button size="small" color="input" onclick={handleCopy}>Copy</Button>
						</div>
					</SplitControl>
				</div>
			{/if}
		{/if}
	</IntegrationConfigContent>
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

	.embed-code-actions {
		display: flex;
		margin-top: 10px;
	}

	.actions-left {
		flex: 1;
		display: flex;
		gap: 6px;
	}

	code {
		font-family: monospace;
		font-size: 0.9em;
	}
</style>
