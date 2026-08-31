<script lang="ts">
	import { IconMessage } from '@hyvor/design/components';
	import { isTempStore } from '../../../lib/temp';
	import IconLock from '@hyvor/icons/IconLock';
	import { getI18n } from '../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();
</script>

<div class="wrap" class:is-temp={$isTempStore}>
	{@render children?.()}

	{#if $isTempStore}
		<div class="temp-notice">
			<div class="overlay"></div>
			<IconMessage message={i18n.t('console.temp.featureDisabled')} icon={IconLock} iconSize={50} />
		</div>
	{/if}
</div>

<style>
	.wrap {
		position: relative;
		height: 100%;
		flex: 1;
	}
	.wrap.is-temp {
		pointer-events: none;
	}
	.temp-notice {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		pointer-events: none;
		z-index: 10;
	}
	.overlay {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		pointer-events: none;
		z-index: 0;
		background-color: #fff;
		opacity: 0.8;
		border-radius: 20px;
	}
</style>
