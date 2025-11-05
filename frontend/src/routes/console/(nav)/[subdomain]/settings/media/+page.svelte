<script lang="ts">
	import {
		Button,
		Callout,
		Loader,
		Modal,
		SplitControl,
		Switch,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import { getS3Storage, testS3Connection, updateS3Integration, deleteS3Storage, type VerifyResults } from './mediaActions';
	import S3Results from './S3Results.svelte';
	import { onMount } from 'svelte';

	let endpointUrl = $state('');
	let bucketName = $state('');
	let accessKey = $state('');
	let secretKey = $state('');
	let region = $state('');
	let pathPrefix = $state('');
	let pathStyleAccess = $state(false);
	let customCdnUrl = $state('');

	let isVerifying = $state(false);
	let isSaving = $state(false);
	let verifyResults = $state(null as VerifyResults | null);
	let hasS3Storage = $state(false);
	let isDisconnecting = $state(false);

	const pass = $derived(
		verifyResults &&
			verifyResults.write &&
			verifyResults.read &&
			verifyResults.delete
	);

	function loadS3Storage() {
		getS3Storage()
			.then((data) => {
				if (data && data.endpoint_url) {
					hasS3Storage = true;
					endpointUrl = data.endpoint_url;
					bucketName = data.bucket_name;
					accessKey = data.access_key;
					secretKey = data.secret_key;
					region = data.region || '';
					pathPrefix = data.path_prefix || '';
					pathStyleAccess = data.path_style_access;
					customCdnUrl = data.cdn_url || '';
				} else {
					hasS3Storage = false;
				}
			})
			.catch((e) => {
				toast.error(e.message);
			});
	}

	function testConnection() {
		isVerifying = true;
		verifyResults = null;
			
		testS3Connection(
			endpointUrl,
			bucketName,
			accessKey,
			secretKey,
			region,
			pathPrefix,
			pathStyleAccess,
			customCdnUrl
		)
			.then((results) => {
				verifyResults = results;
			})
			.catch((e) => {
				toast.error(e.message);
				isVerifying = false;
			});
	}

	function connect() {
		isSaving = true;

		updateS3Integration(
			endpointUrl,
			bucketName,
			accessKey,
			secretKey,
			region,
			pathPrefix,
			pathStyleAccess,
			customCdnUrl
		)
			.then(() => {
				hasS3Storage = true;
				isVerifying = false;
				isSaving = false;
				toast.success('S3 storage connected successfully');
			})
			.catch((e) => {
				toast.error(e.message);
				isSaving = false;
			});
	}

	function disconnectS3() {
		isDisconnecting = true;
		deleteS3Storage()
			.then(() => {
				hasS3Storage = false;
				toast.success('S3 storage disconnected. Media files are being transferred back to Hyvor Blogs.');
				// Clear form fields
				endpointUrl = '';
				bucketName = '';
				accessKey = '';
				secretKey = '';
				region = '';
				pathPrefix = '';
				pathStyleAccess = false;
				customCdnUrl = '';
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				isDisconnecting = false;
			});
	}

	onMount(() => {
		loadS3Storage();
	});
</script>

<div>
	{#if !hasS3Storage}
		<Callout type="info">
			Your media is currently hosted by Hyvor Blogs. You can set up your own S3-compatible storage.
		</Callout>
	{:else}
		<Callout type="success">
			Your media is currently stored in your custom S3 storage.
		</Callout>
	{/if}
	
	<SplitControl
		label="S3 Storage"
		caption="Use your own S3-compatible storage to save media files."
	>
		{#snippet nested()}
			<SplitControl label="S3 Endpoint URL">
				<TextInput block bind:value={endpointUrl} />
			</SplitControl>
			<SplitControl label="Bucket Name">
				<TextInput block bind:value={bucketName} />
			</SplitControl>
			<SplitControl label="Access Key">
				<TextInput block bind:value={accessKey} />
			</SplitControl>
			<SplitControl label="Secret Key">
				<TextInput block bind:value={secretKey} />
			</SplitControl>
			<SplitControl label="Region (optional)">
				<TextInput block bind:value={region} />
			</SplitControl>
			<SplitControl label="Path Prefix (optional)">
				<TextInput block bind:value={pathPrefix} />
			</SplitControl>
			<SplitControl label="Path-style Access">
				<Switch bind:checked={pathStyleAccess} />
			</SplitControl>
			<SplitControl
				label="Custom CDN URL (optional)"
				caption="Set this if you have a custom CDN URL for your S3 bucket."
			>
				<TextInput block bind:value={customCdnUrl} />
			</SplitControl>

			<div class="button-group">
				<Button on:click={testConnection}>Test & Connect</Button>
				{#if hasS3Storage}
					<Button 
						color="red" 
						variant="outline" 
						on:click={disconnectS3}
						disabled={isDisconnecting}
					>
						{isDisconnecting ? 'Disconnecting...' : 'Disconnect & Use Platform Storage'}
					</Button>
				{/if}
			</div>
		{/snippet}
	</SplitControl>
</div>

<Modal
	bind:show={isVerifying}
	title="Connect S3 Storage"
	on:confirm={connect}
	footer={{
		confirm:
			verifyResults === null
				? false
				: {
						text: isSaving ? 'Connecting...' : 'Connect',
						props: {
							disabled: pass === false || isSaving
						}
					}
	}}
>
	{#if verifyResults === null}
		<Loader block>Verifying your S3 settings...</Loader>
	{:else}
		<S3Results results={verifyResults} />
	{/if}
</Modal>

<style>
	div {
		padding: 30px;
	}

	.button-group {
		display: flex;
		gap: 10px;
		padding: 0;
	}
</style>
