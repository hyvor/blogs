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
	<div class="icon {color}" aria-hidden="true">
		<SvelteComponent size={40} />
	</div>
	<h4 class="title">{title}</h4>
	<p class="description">{description}</p>
</li>

<style>
	.feature {
		width: calc(33.33% - 15px);
		list-style: none;
	}

	.icon.green {
		color: var(--green);
	}
	.icon.blue {
		color: var(--blue);
	}
	.icon.orange {
		color: var(--orange);
	}
	.icon.red {
		color: var(--red);
	}

	.title {
		font-weight: 600;
		font-size: 20px;
		margin: 10px 0 0;
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
	}
</style>
