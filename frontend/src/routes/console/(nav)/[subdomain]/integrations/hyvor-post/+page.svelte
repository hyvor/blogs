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
		type HyvorPostIntegrationData
	} from './hyvorPostActions';
	import { onMount } from 'svelte';
	import IntergrationTopNotice from '../components/IntergrationTopNotice.svelte';
	import IntegrationConfigContent from '../components/IntegrationConfigContent.svelte';

	const DEFAULT_EMBED_CODE = `<script src="https://post.hyvor.com/form/form.js" type="module" async><\/script>
<hyvor-post-form newsletter-id="{newsletter-id}"></hyvor-post-form>`;

	let isLoading = $state(true);
	let data: HyvorPostIntegrationData | undefined = $state();
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
				data = { enabled: true, data: res } as HyvorPostIntegrationData;
				embedCode = res.embed_code;
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
				data = { enabled: false, data: undefined };
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
				if (data?.enabled) {
					data = { enabled: true, data: res } as HyvorPostIntegrationData;
				}
				embedCode = res.embed_code;
				toast.success('Embed code saved', { id: toastId });
			})
			.catch((err) =>
				toast.error(err.message || 'Failed to save embed code', { id: toastId })
			);
	}

	function handleResetEmbedCode() {
		embedCode = DEFAULT_EMBED_CODE;
	}

	let isEmbedCodeDirty = $derived(data?.enabled === true && embedCode !== data.data.embed_code);
	let isEmbedCodeDefault = $derived(embedCode === DEFAULT_EMBED_CODE);

	onMount(() => {
		loadHyvorPost()
			.then((res) => {
				data = res;
				if (res.enabled) {
					embedCode = res.data.embed_code;
				}
			})
			.catch(() => toast.error('Failed to load Hyvor Post integration data'))
			.finally(() => (isLoading = false));
	});
</script>

<IntergrationTopNotice>
	<a href="https://post.hyvor.com" target="_blank" class="hds-link"> Hyvor Post </a> is a privacy-first
	email newsletter platform. Connecting it here creates a dedicated newsletter for this blog.
</IntergrationTopNotice>

<IntegrationConfigContent>
	{#if isLoading}
		<Loader full />
	{:else if data}
		<SplitControl label="Hyvor Post Connection">
			{#if data.enabled}
				<div class="connection-status">
					This blog is connected to newsletter <strong>{data.data.subdomain}</strong> in Hyvor
					Post.
				</div>

				<Button
					as="a"
					href={`https://post.hyvor.com/console/${data.data.newsletter_id}`}
					target="_blank"
					size="small"
					style="margin-right:6px;"
				>
					Go to Hyvor Post Console
				</Button>

				<Button color="red" size="small" on:click={handleDisconnect}>Disconnect</Button>
			{:else}
				<div class="connection-status">
					<Tag>Not Connected</Tag>
				</div>

				<Button onclick={handleConnect}>Connect Now</Button>
			{/if}
		</SplitControl>

		{#if data.enabled}
			<div class="embed-code">
				<SplitControl label="Embed Code">
					{#snippet caption()}
						<div>Paste this code in your theme to show the newsletter signup form.</div>
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
							<Button size="small" variant="invisible" on:click={handleResetEmbedCode}
								>Reset to default</Button
							>
						</div>
					{/if}
				</SplitControl>
			</div>
		{/if}
	{/if}
</IntegrationConfigContent>

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
