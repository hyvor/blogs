<script lang="ts">
	interface FaqItem {
		q: string;
		/** answer content as HTML - also used verbatim as the rich schema's answer text */
		a: string;
	}

	interface Props {
		items: FaqItem[];
		columns?: 1 | 2;
	}

	let { items, columns = 2 }: Props = $props();

	const uid = $props.id();

	// which items go in which column (left-to-right, top-to-bottom within each column),
	// carrying each item's original index along so column splitting doesn't affect it
	const cols = $derived.by(() => {
		const indexed = items.map((item, index) => ({ item, index }));
		if (columns === 1) return [indexed];
		const mid = Math.ceil(indexed.length / 2);
		return [indexed.slice(0, mid), indexed.slice(mid)];
	});

	// independent open/close state per item, first one open by default
	let openStates = $state(items.map((_, i) => i === 0));

	function toggle(i: number) {
		openStates[i] = !openStates[i];
	}

	// FAQPage rich schema, generated straight from the same array that renders the accordion
	// (Google's FAQPage guidelines allow a limited set of formatting HTML in the answer text)
	const richSchema = $derived({
		'@context': 'https://schema.org',
		'@type': 'FAQPage',
		mainEntity: items.map((item) => ({
			'@type': 'Question',
			name: item.q,
			acceptedAnswer: {
				'@type': 'Answer',
				text: item.a
			}
		}))
	});

	const richSchemaScript = $derived(
		'<' + 'script type="application/ld+json">' + JSON.stringify(richSchema) + '<' + '/script>'
	);
</script>

<svelte:head>
	{@html richSchemaScript}
</svelte:head>

<div class="faqs" class:single={columns === 1}>
	{#each cols as col, colIndex (colIndex)}
		<div class="faq-col">
			{#each col as { item, index } (item.q)}
				<div class="faq" class:open={openStates[index]}>
					<h3 class="heading">
						<button
							class="trigger"
							id="faq-trigger-{uid}-{index}"
							onclick={() => toggle(index)}
							aria-expanded={openStates[index]}
							aria-controls="faq-panel-{uid}-{index}"
						>
							<span class="q">{item.q}</span>
							<span class="toggle-icon" aria-hidden="true">
								<span class="line horizontal"></span>
								<span class="line vertical"></span>
							</span>
						</button>
					</h3>

					<div
						class="body"
						class:open={openStates[index]}
						role="region"
						id="faq-panel-{uid}-{index}"
						aria-labelledby="faq-trigger-{uid}-{index}"
					>
						<div class="body-inner">
							<div class="a">{@html item.a}</div>
						</div>
					</div>
				</div>
			{/each}
		</div>
	{/each}
</div>

<style>
	.faqs {
		display: flex;
		gap: 0 60px;
	}

	.faqs.single {
		display: block;
	}

	.faq-col {
		flex: 1;
		min-width: 0;
	}

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
		transition:
			transform 0.3s cubic-bezier(0.65, 0, 0.35, 1),
			opacity 0.2s ease;
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
		transition:
			opacity 0.25s ease,
			transform 0.25s ease;
	}

	.body.open .a {
		opacity: 1;
		transform: translateY(0);
		transition-delay: 0.08s;
	}

	/* answers are raw HTML (@html) - style plain <a> tags to match the design
	   system's Link component, which the old snippet-based answers used */
	.a :global(a) {
		color: var(--link);
		text-decoration: underline;
	}

	@media (max-width: 768px) {
		.faqs {
			flex-direction: column;
			gap: 0;
		}
	}
</style>
