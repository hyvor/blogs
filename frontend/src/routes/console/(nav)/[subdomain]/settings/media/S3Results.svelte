<script lang="ts">
	import { Callout, Tag } from '@hyvor/design/components';
	import type { VerifyResults } from './mediaActions';

	let { results }: { results: VerifyResults } = $props();

	const pass = $derived(
		results.write && results.read && results.delete
	);
</script>

<div>
	{@render row('Write', 'Creating and writing to a file', results.write, results.errors.write)}
	{@render row('Read', 'Reading from a file', results.read, results.errors.read)}
	<!-- {@render row(
		'Visibility',
		'Changing file visibility to public',
		results.visibility,
		results.errors.visibility
	)}
	{@render row(
		'Public Access',
		'Accessing file with public URL',
		results.public_access,
		results.errors.public_access
	)} -->
	{@render row('Delete', 'Deleting a file', results.delete, results.errors.delete)}
</div>

{#if !pass}
	<Callout type="danger">
		Some tests failed. Make sure you have configured your S3 bucket and credentials correctly.
	</Callout>
{/if}

{#snippet row(title: string, description: string, pass: boolean, error: string | undefined)}
	<div class="row-wrap">
		<div class="row">
			<div class="left">
				<div class="title">{title}</div>
				<div class="description">{description}</div>
			</div>
			<div>
				{#if pass}
					<div>
						<Tag color="green">OK</Tag>
					</div>
				{:else}
					<div>
						<Tag color="red">Failed</Tag>
					</div>
				{/if}
			</div>
		</div>
		{#if error}
			<div class="error">
				<strong>Error:</strong>
				{error}
			</div>
		{/if}
	</div>
{/snippet}

<style>
	.row-wrap {
		padding: 10px 0;
	}
	.row {
		display: flex;
		align-items: center;
	}

	.left {
		display: flex;
		flex-direction: column;
		flex: 1;
	}

	.title {
		font-weight: bold;
	}

	.description {
		color: var(--text-light);
		font-size: 14px;
	}

	.error {
		color: var(--red-dark);
		font-size: 14px;
		margin-top: 5px;
		font-style: italic;
	}
</style>
