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
				toast.error(error);
			})
			.finally(() => {
				loading = false;
			});
	});
</script>

{#if loading}
	<Loader padding={150} />
{:else}
	<div class="usage">
		<UsageBar name="Users" data={usage.users} />
		<UsageBar name="Media Storage" data={usage.storage} bytes={true} />
		<UsageBar
			name="Auto-Translate Characters (this month)"
			data={usage.auto_translate_chars}
			zero={true}
		/>
		<UsageBar name="GPT Tokens (this month)" data={usage.ai_tokens} zero={true} />
	</div>
{/if}
