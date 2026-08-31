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
		loadHyvorPost,
		connectHyvorPost,
		disconnectHyvorPost,
		updateHyvorPostEmbedCode,
		type HyvorPostIntegration
	} from './hyvorPostActions';
	import { onMount } from 'svelte';
	import IntergrationTopNotice from '../components/IntergrationTopNotice.svelte';
	import IntegrationConfigContent from '../components/IntegrationConfigContent.svelte';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import { consoleUrlWithBlog } from '../../../../lib/consoleUrl';
	import { getConfig } from '../../../../lib/config';
	import IntegrationNotAvailable from '../components/IntegrationNotAvailable.svelte';
	import { setHyvorPostIntegrationState } from '../../../../lib/stores/blogStore';
	import DisconnectConfirm from './DisconnectConfirm.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	let isLoading = $state(true);
	let data: HyvorPostIntegration | null = $state(null);
	let embedCode = $state('');

	async function handleConnect() {
		const confirmed = await confirm({
			title: i18n.t('console.integrations.hyvorPost.connectTitle'),
			content: i18n.t('console.integrations.hyvorPost.connectContent'),
			confirmText: i18n.t('console.integrations.hyvorTalk.connectConfirm'),
			autoClose: false
		});

		if (!confirmed) return;

		confirmed.loading('Creating a newsletter on Hyvor Post...');

		connectHyvorPost()
			.then((res) => {
				setFromIntegration(res);
				setHyvorPostIntegrationState(res.newsletter_id);
				toast.success(i18n.t('console.integrations.hyvorPost.connected'));
			})
			.catch((err) => toast.error(err.message || 'Failed to connect to Hyvor Post'))
			.finally(() => confirmed.close());
	}

	async function handleDisconnect() {
		const confirmed = await confirm({
			title: i18n.t('console.integrations.hyvorPost.disconnectTitle'),
			content: DisconnectConfirm,
			confirmText: i18n.t('console.integrations.hyvorPost.disconnectConfirm'),
			danger: true
		});

		if (!confirmed) return;

		const toastId = toast.loading(i18n.t('console.integrations.hyvorPost.disconnecting'));

		disconnectHyvorPost()
			.then(() => {
				setFromIntegration(null);
				setHyvorPostIntegrationState(null);
				toast.success(i18n.t('console.integrations.hyvorPost.disconnected'), { id: toastId });
			})
			.catch((err) =>
				toast.error(err.message || 'Failed to disconnect from Hyvor Post', { id: toastId })
			);
	}

	function handleSaveEmbedCode() {
		const toastId = toast.loading(i18n.t('console.integrations.common.savingEmbedCode'));

		updateHyvorPostEmbedCode(embedCode)
			.then((res) => {
				setFromIntegration(res);
				toast.success(i18n.t('console.integrations.common.embedCodeSaved'), { id: toastId });
			})
			.catch((err) => toast.error(err.message || 'Failed to save embed code', { id: toastId }));
	}

	function handleResetEmbedCode() {
		embedCode = data!.embed_default_code;
	}

	let isEmbedCodeDirty = $derived.by(() => data && embedCode !== data.embed_code);
	let isEmbedCodeDefault = $derived.by(() => embedCode === data?.embed_default_code);

	onMount(() => {
		loadHyvorPost()
			.then((res) => {
				setFromIntegration(res.data);
			})
			.catch(() => toast.error(i18n.t('console.integrations.hyvorPost.failedToLoad')))
			.finally(() => (isLoading = false));
	});

	function setFromIntegration(int: HyvorPostIntegration | null) {
		data = int;
		if (int) {
			embedCode = int.embed_code;
		}
	}

	function handleCopy() {
		navigator.clipboard.writeText(embedCode).then(() => {
			toast.success(i18n.t('console.integrations.common.embedCodeCopied'));
		});
	}
</script>

{#if getConfig().deployment === 'on-prem'}
	<IntegrationNotAvailable>
		<T
			key="console.integrations.hyvorPost.notAvailable"
			params={{
				link: {
					element: 'a',
					props: { href: consoleUrlWithBlog('/settings/comments'), class: 'hds-link' }
				}
			}}
		/>
	</IntegrationNotAvailable>
{:else}
	<IntergrationTopNotice>
		<T
			key="console.integrations.hyvorPost.topNotice"
			params={{
				link: {
					element: 'a',
					props: { href: 'https://post.hyvor.com', target: '_blank', class: 'hds-link' }
				}
			}}
		/>
	</IntergrationTopNotice>

	<IntegrationConfigContent>
		{#if isLoading}
			<Loader full />
		{:else}
			<SplitControl label={i18n.t('console.integrations.hyvorPost.connection')}>
				{#if data}
					<div class="connection-status">
						<T
							key="console.integrations.hyvorPost.connectedToNewsletter"
							params={{ strong: { element: 'strong' }, newsletterId: data.newsletter_id }}
						/>
					</div>

					<Button
						as="a"
						href={consoleUrlWithBlog('/newsletter')}
						size="small"
						style="margin-right:6px;"
					>
						{i18n.t('console.integrations.hyvorPost.manageNewsletter')}
						{#snippet end()}
							&rarr;
						{/snippet}
					</Button>

					<Button
						as="a"
						href={`https://post.hyvor.com/console?newsletter_id=${data.newsletter_id}`}
						target="_blank"
						size="small"
						style="margin-right:6px;"
						variant="outline"
					>
						{i18n.t('console.integrations.hyvorPost.postConsole')}
						{#snippet end()}
							<IconBoxArrowUpRight size={10} />
						{/snippet}
					</Button>

					<Button color="red" size="small" on:click={handleDisconnect}
						>{i18n.t('console.integrations.hyvorTalk.disconnect')}</Button
					>
				{:else}
					<Button onclick={handleConnect}
						>{i18n.t('console.integrations.hyvorTalk.connectNow')}</Button
					>
				{/if}
			</SplitControl>

			{#if data}
				<div class="embed-code">
					<SplitControl label={i18n.t('console.integrations.common.embedCode')} column>
						{#snippet caption()}
							<div>
								This code is automatically added to your blog's <a
									href="/docs/themes-templates#placeholders"
									class="hds-link"
									target="_blank"
									><code>_newsletter</code>
									variable</a
								>, which is usually placed below the post content (depending on the theme).
							</div>
						{/snippet}

						<Textarea bind:value={embedCode} rows={10} block />

						<div class="embed-code-actions">
							<div class="actions-left">
								{#if isEmbedCodeDirty}
									<Button size="small" on:click={handleSaveEmbedCode}
										>{i18n.t('console.common.save')}</Button
									>
								{/if}
								{#if !isEmbedCodeDefault}
									<Button size="small" variant="invisible" on:click={handleResetEmbedCode}
										>Reset to default</Button
									>
								{/if}
							</div>
							<Button size="small" color="input" onclick={handleCopy}
								>{i18n.t('console.common.copy')}</Button
							>
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
