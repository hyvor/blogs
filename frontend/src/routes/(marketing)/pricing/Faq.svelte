<script lang="ts">
	interface Props {
		q: string;
		open?: boolean;
		children?: import('svelte').Snippet;
	}

	let { q, open = false, children }: Props = $props();

	const uid = $props.id();

	function toggle() {
		open = !open;
	}
</script>

<div class="faq" class:open>
	<h3 class="heading">
		<button
			class="trigger"
			id="faq-trigger-{uid}"
			onclick={toggle}
			aria-expanded={open}
			aria-controls="faq-panel-{uid}"
		>
			<span class="q">{q}</span>
			<span class="toggle-icon" aria-hidden="true">
				<span class="line horizontal"></span>
				<span class="line vertical"></span>
			</span>
		</button>
	</h3>

	<div class="body" class:open role="region" id="faq-panel-{uid}" aria-labelledby="faq-trigger-{uid}">
		<div class="body-inner">
			<div class="a">{@render children?.()}</div>
		</div>
	</div>
</div>

<style>
	.faq {
		border-bottom: 1px solid var(--border);
	}

	.heading {
		margin: 0;
		font-size: inherit;
		font-weight: inherit;
	}

	.trigger {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 20px;
		width: 100%;
		padding: 20px 0;
		background: none;
		border: none;
		cursor: pointer;
		text-align: left;
		color: inherit;
		font: inherit;
	}

	.q {
		font-size: 16px;
		font-weight: 600;
		letter-spacing: -0.01em;
	}

	.toggle-icon {
		position: relative;
		flex-shrink: 0;
		width: 24px;
		height: 24px;
		border-radius: 50%;
		background: var(--accent-light-mid);
		color: var(--accent);
		transition: transform 0.3s cubic-bezier(0.65, 0, 0.35, 1);
	}

	.faq.open .toggle-icon {
		transform: rotate(180deg);
	}

	.line {
		position: absolute;
		top: 50%;
		left: 50%;
		background: currentColor;
		border-radius: 2px;
		transform: translate(-50%, -50%);
		transition: transform 0.3s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.2s ease;
	}

	.line.horizontal {
		width: 12px;
		height: 2px;
	}

	.line.vertical {
		width: 2px;
		height: 12px;
	}

	.faq.open .line.vertical {
		transform: translate(-50%, -50%) rotate(90deg);
		opacity: 0;
	}

	/* CSS grid row trick: content stays in DOM for SEO, height animates via grid */
	.body {
		display: grid;
		grid-template-rows: 0fr;
		transition: grid-template-rows 0.3s cubic-bezier(0.65, 0, 0.35, 1);
	}

	.body.open {
		grid-template-rows: 1fr;
	}

	.body-inner {
		overflow: hidden;
	}

	.a {
		padding: 0 30px 22px 0;
		font-size: 15px;
		line-height: 1.6;
		color: var(--text-light);
		opacity: 0;
		transform: translateY(-6px);
		transition: opacity 0.25s ease, transform 0.25s ease;
	}

	.body.open .a {
		opacity: 1;
		transform: translateY(0);
		transition-delay: 0.08s;
	}
</style>
