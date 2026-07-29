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

	const DEFAULT_EMBED_CODE = `<script src="https://post.hyvor.com/form/form.js" type="module" async><\/script>
<hyvor-post-form newsletter-id="{newsletter-id}"></hyvor-post-form>`;

	let isLoading = $state(true);
	let data: HyvorPostIntegration | null = $state(null);
	let embedCode = $state('');

	async function handleConnect() {
		const confirmed = await confirm({
			title: 'Connect Hyvor Post',
			content:
				'A new newsletter will be created on Hyvor Post under your organization, and this blog will be connected to it.',
			confirmText: 'Yes, Connect',
			autoClose: false
		});

		if (!confirmed) return;

		confirmed.loading('Creating a newsletter on Hyvor Post...');

		connectHyvorPost()
			.then((res) => {
				setFromIntegration(res);
				setHyvorPostIntegrationState(res.newsletter_id);
				toast.success('Hyvor Post connected successfully');
			})
			.catch((err) => toast.error(err.message || 'Failed to connect to Hyvor Post'))
			.finally(() => confirmed.close());
	}

	async function handleDisconnect() {
		const confirmed = await confirm({
			title: 'Disconnect Hyvor Post',
			content:
				'Are you sure you want to disconnect this blog from Hyvor Post? This will not delete your newsletter on Hyvor Post. You will have to delete it manually from the Hyvor Post Console.',
			confirmText: 'Yes, Disconnect',
			danger: true
		});

		if (!confirmed) return;

		const toastId = toast.loading('Disconnecting from Hyvor Post...');

		disconnectHyvorPost()
			.then(() => {
				setFromIntegration(null);
				setHyvorPostIntegrationState(null);
				toast.success('Hyvor Post disconnected successfully', { id: toastId });
			})
			.catch((err) =>
				toast.error(err.message || 'Failed to disconnect from Hyvor Post', { id: toastId })
			);
	}

	function handleSaveEmbedCode() {
		const toastId = toast.loading('Saving embed code...');

		updateHyvorPostEmbedCode(embedCode)
			.then((res) => {
				setFromIntegration(res);
				toast.success('Embed code saved', { id: toastId });
			})
			.catch((err) =>
				toast.error(err.message || 'Failed to save embed code', { id: toastId })
			);
	}

	function handleResetEmbedCode() {
		embedCode = DEFAULT_EMBED_CODE;
	}

	let isEmbedCodeDirty = $derived.by(() => data && embedCode !== data.embed_code);
	let isEmbedCodeDefault = $derived(embedCode === DEFAULT_EMBED_CODE);

	onMount(() => {
		loadHyvorPost()
			.then((res) => {
				setFromIntegration(res.data);
			})
			.catch(() => toast.error('Failed to load Hyvor Post integration data'))
			.finally(() => (isLoading = false));
	});

	function setFromIntegration(int: HyvorPostIntegration | null) {
		data = int;
		if (int) {
			embedCode = int.embed_code;
		}
	}
</script>

{#if getConfig().deployment === 'on-prem'}
	<IntegrationNotAvailable>
		Hyvor Post integration is not available in self-hosted deployments. However, you can easily
		embed Hyvor Post or another newsletter signup form by adding the embed code directly in <a
			href={consoleUrlWithBlog('/settings/comments')}
			class="hds-link">Settings &rarr; Comments & Newsletters</a
		>.
	</IntegrationNotAvailable>
{:else}
	<IntergrationTopNotice>
		<a href="https://post.hyvor.com" target="_blank" class="hds-link"> Hyvor Post </a> is a privacy-first
		email newsletter platform. All Hyvor Blogs plans include a free complimentary license for Hyvor
		Post.
	</IntergrationTopNotice>

	<IntegrationConfigContent>
		{#if isLoading}
			<Loader full />
		{:else}
			<SplitControl label="Hyvor Post Connection">
				{#if data}
					<div class="connection-status">
						This blog is connected to a newsletter (ID: <strong
							>{data.newsletter_id}</strong
						>) in Hyvor Post.
					</div>

					<Button
						as="a"
						href={consoleUrlWithBlog('/newsletter')}
						size="small"
						style="margin-right:6px;"
					>
						Manage Newsletter
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
						Hyvor Post Console
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
								Paste this code in your theme to show the newsletter signup form.
							</div>
						{/snippet}

						<Textarea bind:value={embedCode} rows={4} block />

						{#if isEmbedCodeDirty}
							<div class="embed-code-actions">
								<Button size="small" on:click={handleSaveEmbedCode}>Save</Button>
								{#if !isEmbedCodeDefault}
									<Button
										size="small"
										variant="invisible"
										on:click={handleResetEmbedCode}>Reset to default</Button
									>
								{/if}
							</div>
						{:else if !isEmbedCodeDefault}
							<div class="embed-code-actions">
								<Button
									size="small"
									variant="invisible"
									on:click={handleResetEmbedCode}>Reset to default</Button
								>
							</div>
						{/if}
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
		gap: 6px;
		margin-top: 10px;
	}
</style>
