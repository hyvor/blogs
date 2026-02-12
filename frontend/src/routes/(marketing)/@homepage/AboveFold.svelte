<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import Hurry from './Hurry.svelte';
	import TrialChecks from '../@components/TrialChecks.svelte';
	import hbIcon from '$lib/img/logo-social.png';

	interface Props {
		title: string;
		subtitle: string;
		integrationIcon?: string | undefined;
	}

	let { title, subtitle, integrationIcon = undefined }: Props = $props();
</script>

<div class="hds-container above-fold">
	<div class="left">
		{#if integrationIcon}
			<div class="int">
				<img src={integrationIcon} alt="Integration icon" class="int-icon" />
				<span>+</span>
				<img src={hbIcon} alt="Hyvor Blogs" class="int-icon" />
			</div>
		{/if}

		<h1>
			{title}
		</h1>

		<h2>
			{subtitle}
		</h2>

		<div class="buttons">
			<Button as="a" href="/console?signup" size="large">Start your blog</Button>

			<Button as="a" href="/customers" size="large" variant="outline">See customer blogs</Button>
		</div>

		<div class="trial-checks">
			<TrialChecks />
		</div>
	</div>

	<div class="right">
		<Hurry />
	</div>
</div>

<style lang="scss">
	.above-fold {
		display: flex;
		padding-top: 75px;

		.left {
			flex: 1;
		}

		.right {
			flex: 1;
			padding-left: 25px;
		}

		.int {
			display: flex;
			align-items: center;
			gap: 15px;
			font-size: 35px;
			margin-bottom: 10px;
			span {
				opacity: 0.2;
			}
		}

		.int-icon {
			width: 50px;
			height: 50px;
			border-radius: 50%;
		}

		h1 {
			margin: 0;
			font-size: 40px;
		}

		h2 {
			font-weight: normal;
			font-size: 20px;
			color: var(--text-light);
			margin: 0;
			padding-top: 20px;
		}

		.buttons {
			display: flex;
			gap: 10px;
			margin-top: 30px;
		}
	}

	.trial-checks {
		margin-top: 25px;
		margin-left: 5px;
	}

	@media (max-width: 992px) {
		.above-fold {
			flex-direction: column;
			text-align: center;
			.right {
				margin: auto;
				margin-top: 30px;
				padding-left: 0;
				width: 500px;
				max-width: 100%;
			}
			.buttons {
				justify-content: center;
			}
		}

		.int {
			justify-content: center;
		}

		.trial-checks {
			margin-left: 0;
			display: flex;
			justify-content: center;
		}
	}
</style>
