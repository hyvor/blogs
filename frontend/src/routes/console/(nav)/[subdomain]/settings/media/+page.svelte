<script lang="ts">
	import {
		Button,
		Loader,
		Modal,
		SplitControl,
		Switch,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import { updateS3Integration, type VerifyResults } from './mediaActions';
	import S3Results from './S3Results.svelte';
	import { on } from 'svelte/events';

	let endpointUrl = $state('https://s3.eu-west-3.amazonaws.com');
	let bucketName = $state('hbstoragetestings');
	let accessKey = $state('AKIAW3MEBKLXRELX6LVN');
	let secretKey = $state('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');
	let region = $state('eu-west-3');
	let pathPrefix = $state('');
	let pathStyleAccess = $state(false);
	let customCdnUrl = $state('');

	let isVerifying = $state(false);
	let verifyResults = $state(null as VerifyResults | null);

	const pass = $derived(
		verifyResults &&
			verifyResults.write &&
			verifyResults.read &&
			verifyResults.delete
	);

	function save(test: boolean = true) {
		isVerifying = true;
		verifyResults = null;

		updateS3Integration(
			endpointUrl,
			bucketName,
			accessKey,
			secretKey,
			region,
			pathPrefix,
			pathStyleAccess,
			customCdnUrl,
			test
		)
			.then((results) => {
				verifyResults = results;
			})
			.catch((e) => {
				toast.error(e.message);
				isVerifying = false;
			})
			.finally(() => {
				// isVerifying = false;
			});
	}
</script>

<div>
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

			<Button on:click={() => save()}>Save</Button>
		{/snippet}
	</SplitControl>
</div>

<Modal
	bind:show={isVerifying}
	title="Connect S3 Storage"
	on:confirm={() => {
		save(false);
	}}
	footer={{
		confirm:
			verifyResults === null
				? false
				: {
						text: 'Connect',
						props: {
							disabled: pass === false
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
</style>
