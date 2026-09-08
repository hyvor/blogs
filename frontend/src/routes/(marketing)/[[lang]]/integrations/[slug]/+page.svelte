<script lang="ts">
	import { Box } from '@hyvor/design/components';
	import GuideHead from '../GuideHead.svelte';
	let { data } = $props();
</script>

<svelte:head>
	<title>{data.title}</title>
	<meta name="description" content={data.description} />
	<link rel="canonical" href={`https://blogs.hyvor.com/integrations/${data.slug}`} />
</svelte:head>

<GuideHead title={data.title} integrationIcon={data.icon} description={data.description} />

<div class="content-wrap">
	<Box>
		<content>
			<data.component />
		</content>
	</Box>
</div>

<style>
	.content-wrap {
		width: 850px;
		max-width: 100%;
		margin: auto;
		margin-top: 40px;
		margin-bottom: 80px;
	}

	content {
		display: block;
		padding: 30px 50px;

		:global(p),
		:global(li) {
			line-height: var(--line-height-content);
		}

		:global(h1:first-child) {
			margin-top: 0;
			font-size: 36px;
			font-weight: 600;
			letter-spacing: -0.03em;
			margin: 0 0 30px;
			position: relative;
			display: table;

			&:after {
				position: absolute;
				content: '';
				bottom: -13px;
				left: 0px;
				width: 30%;
				height: 3px;
				background: var(--accent);
				margin-top: 10px;
			}
		}

		:global(a:not(.no-link-color a)) {
			color: var(--link);
			text-decoration: underline;
		}

		:global(li) {
			margin-bottom: 8px;
		}

		:global(.table) {
			margin: 20px 0;
		}

		:global(code) {
			font-size: 14px;
			padding: 0.2em 0.4em;
			display: inline-block;
			background-color: #f4f2f0;
			color: #905;
			font-family: inherit;
			border-radius: 4px;
			line-height: normal;
			font-weight: 400;
		}

		:global(h1),
		:global(h2),
		:global(h3),
		:global(h4),
		:global(h5),
		:global(h6) {
			position: relative;
			margin: 20px 0;
		}

		:global(h1) {
			font-size: 2em;
		}
		:global(h2) {
			font-size: 1.5em;
		}
		:global(h3) {
			font-size: 1.3em;
		}
		:global(h4) {
			font-size: 1.2em;
		}
		:global(h5) {
			font-size: 1.1em;
		}
		:global(h6) {
			font-size: 1em;
		}
	}

	:global(:root.dark) content :global(code) {
		background-color: #282c34;
		color: #e06c75;
	}

	@media (max-width: 992px) {
		content {
			padding: 20px 25px;
		}
	}
</style>
