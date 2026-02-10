<script>
	import { onMount } from 'svelte';
	import { blogStore } from '../../../lib/stores/blogStore';
	import { isTempStore } from '../../../lib/temp';
	import { Button } from '@hyvor/design/components';

	let timeRemaining = $state(0);

	let timeRemainingFormatted = $derived(
		new Date(timeRemaining * 1000).toISOString().substring(11, 19)
	);

	/* beforeNavigate(navigation => {
        if ($isTempStore) {
            if (
                navigation.to &&
                navigation.from?.url.searchParams.has('temp') && 
                !navigation.to.url.searchParams.has('temp')
            ) {
                console.log(navigation);
                navigation.cancel();
                goto(navigation.to.url.pathname + '?temp');
            }
        }
    }); */

	onMount(() => {
		if (!$isTempStore) return;

		document.documentElement.classList.add('has-top-offset');

		// 24 hours
		timeRemaining = 86400 - Math.floor(Date.now() / 1000 - $blogStore.created_at);

		const interval = setInterval(() => {
			timeRemaining -= 1;
			if (timeRemaining < 0) {
				clearInterval(interval);
				location.href = '/console';
			}
		}, 1000);

		return () => {
			clearInterval(interval);
		};
	});
</script>

{#if $isTempStore}
	<div class="notice">
		<div>
			This is a <strong>temporary blog</strong>. It will be deleted in
			<strong>{timeRemainingFormatted}</strong>.
			<Button
				color="red"
				variant="fill-light"
				size="small"
				style="margin-left:10px"
				as="a"
				href="/console?signup"
				data-sveltekit-reload
			>
				Start a live blog now
			</Button>
		</div>
	</div>
{/if}

<style lang="scss">
	.notice {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: var(--top-offset);
		display: flex;
		align-items: center;
		padding: 0 20px;
		background-color: var(--red-dark);
		color: var(--text-white);
		font-size: 14px;
		z-index: 1000000000000000;
	}

	:global(:root.has-top-offset) {
		--top-offset: 40px;
		:global(body) {
			margin-top: var(--top-offset);
		}
	}
</style>
