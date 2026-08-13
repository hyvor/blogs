<script lang="ts">
	import type { Component } from 'svelte';

	interface Props {
		icon: Component;
		title: string;
		description: string;
		color: 'green' | 'blue' | 'orange' | 'red';
	}

	let { icon, title, description, color }: Props = $props();

	const SvelteComponent = $derived(icon);
</script>

<li class="feature">
	<div class="icon-bg {color}" aria-hidden="true">
		<SvelteComponent size={52} />
	</div>
	<div class="content">
		<h4 class="title">{title}</h4>
		<p class="description">{description}</p>
	</div>
</li>

<style>
	.feature {
		position: relative;
		width: calc(33.33% - 15px);
		list-style: none;
		overflow: hidden;
	}

	.content {
		position: relative;
		z-index: 1;
	}

	/* a large, faint watermark of the feature's own icon, sitting behind the
	   title/description rather than as a small marker above them */
	.icon-bg {
		position: absolute;
		top: 50%;
		right: -8px;
		transform: translateY(-50%);
		z-index: 0;
		opacity: 0.14;
		pointer-events: none;
	}

	.icon-bg.green {
		color: var(--green);
	}
	.icon-bg.blue {
		color: var(--blue);
	}
	.icon-bg.orange {
		color: var(--orange);
	}
	.icon-bg.red {
		color: var(--red);
	}

	.title {
		font-weight: 600;
		font-size: 20px;
		margin: 0;
	}

	.description {
		font-size: 16px;
		margin: 10px 0 0;
	}

	@media (max-width: 992px) {
		.feature {
			width: 100%;
			text-align: center;
		}

		.icon-bg {
			display: none;
		}
	}
</style>
