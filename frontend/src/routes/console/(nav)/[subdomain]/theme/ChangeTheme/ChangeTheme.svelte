<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import SelectThemeModal from './SelectThemeModal.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	let show = $state(false);
</script>

<Button size="small" on:click={() => (show = true)}>{i18n.t('console.theme.change')}</Button>

{#if show}
	<SelectThemeModal bind:show />
{/if}
