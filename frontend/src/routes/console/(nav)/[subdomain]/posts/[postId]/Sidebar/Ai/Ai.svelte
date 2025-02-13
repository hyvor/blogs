<script>
	import LicenseRequired from '../../../../../billing/LicenseRequired.svelte';
	import AiChat from './AiChat.svelte';
</script>

<LicenseRequired license="aiTokens">
	{#snippet upgradeText()}
		<div>
			AI chat is available on the <b>Growth plan</b> and above. Upgrade now to use GPT to generate
			and improve your content.
		</div>
	{/snippet}

	<AiChat />
</LicenseRequired>
