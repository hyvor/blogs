<script lang="ts">
	import { onMount } from 'svelte';
	import UsageBar from './UsageBar.svelte';
	import { getUsage, type UsageData } from '../billingActions';
	import { Loader, toast } from '@hyvor/design/components';

	let usage: UsageData;
	let loading = true;

	onMount(() => {
		getUsage()
			.then((data) => {
				usage = data;
			})
			.catch((error) => {
				toast.error(error.message);
			})
			.finally(() => {
				loading = false;
			});
	});
</script>

{#if loading}
	<Loader padding={100} block />
{:else}
	<div class="usage">
		<UsageBar name="Blogs" data={usage.blogs} />
		<UsageBar name="Media Storage" data={usage.storage} bytes={true} />
		<UsageBar name="AI Usage (this month)" data={usage.ai} percent={true} />
	</div>
{/if}
