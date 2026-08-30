<script lang="ts">
	const products = [
		{
			name: 'Hyvor Talk',
			component: 'talk',
			side: 'left',
			arrowLabel: 'Our commenting platform',
			tag: 'Complimentary license'
		},
		{
			name: 'Hyvor Post',
			component: 'post',
			side: 'right',
			arrowLabel: 'Our newsletter platform',
			tag: 'Complimentary license'
		}
	];
</script>

<div class="suite-mockup">
	<div class="join-scene">
		<div class="join-row">
			{#each products as p}
				<div class="satellite-col {p.side}">
					<div class="satellite">
						<img
							src="https://hyvor.com/api/public/logo/{p.component}.svg"
							alt={p.name}
							width="40"
							height="40"
						/>
					</div>
					<span class="sat-name">{p.name}</span>
					<span class="sat-tag">{p.tag}</span>
				</div>
				{#if p.side === 'left'}
					<div class="hub">
						<img src="/logo.svg" alt="Hyvor Blogs" width="46" height="46" />
					</div>
				{/if}
			{/each}
		</div>

		<div class="bottom-arrows-row">
			{#each products as p}
				<div class="annotate-col">
					{#if p.side === 'left'}
						<svg class="arrow-svg bottom-arrow" viewBox="0 0 60 90">
							<path class="arrow-path" d="M38,85 C20,65 20,25 28,6 M19,17 L28,6 L37,16" />
						</svg>
					{:else}
						<svg class="arrow-svg bottom-arrow" viewBox="0 0 60 90">
							<path class="arrow-path" d="M22,85 C40,65 40,25 32,6 M41,17 L32,6 L23,16" />
						</svg>
					{/if}
					<span class="arrow-label">{p.arrowLabel}</span>
				</div>
				{#if p.side === 'left'}
					<div class="hub-spacer"></div>
				{/if}
			{/each}
		</div>
	</div>
</div>

<style>
	.suite-mockup {
		display: flex;
		justify-content: center;
	}

	.join-scene {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 4px;
		padding: 40px 20px;
	}

	.join-row {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 24px;
	}

	.annotate-col {
		width: 90px;
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 2px;
	}

	.arrow-label {
		font-size: 11px;
		font-weight: 600;
		color: var(--text-light);
		text-align: center;
		line-height: 1.3;
	}

	.arrow-svg {
		width: 38px;
		height: 58px;
		color: color-mix(in srgb, var(--text) 40%, transparent);
	}

	.arrow-path {
		fill: none;
		stroke: currentColor;
		stroke-width: 2;
		stroke-linecap: round;
		stroke-linejoin: round;
	}

	.hub {
		width: 100px;
		height: 100px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: color-mix(in srgb, var(--accent) 14%, transparent);
		flex-shrink: 0;
	}

	.satellite-col {
		width: 90px;
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 8px;
		flex-shrink: 0;
	}

	.satellite {
		width: 68px;
		height: 68px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.satellite-col.left .satellite {
		animation: satellite-jump-left 3.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
	}

	.satellite-col.right .satellite {
		animation: satellite-jump-right 3.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
	}

	@keyframes satellite-jump-left {
		0%,
		3% {
			transform: translateX(-70px) translateY(8px) scale(0.75);
			opacity: 0;
		}
		8% {
			opacity: 1;
		}
		16% {
			transform: translateX(8px) translateY(-9px) scale(1.07);
		}
		22% {
			transform: translateX(-3px) translateY(2px) scale(0.97);
		}
		28%,
		100% {
			transform: translateX(0) translateY(0) scale(1);
			opacity: 1;
		}
	}

	@keyframes satellite-jump-right {
		0%,
		3% {
			transform: translateX(70px) translateY(8px) scale(0.75);
			opacity: 0;
		}
		8% {
			opacity: 1;
		}
		16% {
			transform: translateX(-8px) translateY(-9px) scale(1.07);
		}
		22% {
			transform: translateX(3px) translateY(2px) scale(0.97);
		}
		28%,
		100% {
			transform: translateX(0) translateY(0) scale(1);
			opacity: 1;
		}
	}

	.sat-name {
		font-size: 12px;
		font-weight: 700;
		color: var(--text);
		white-space: nowrap;
		opacity: 0;
		animation: fade-up-in 3.4s ease-out forwards;
	}

	.sat-tag {
		font-size: 10px;
		font-weight: 600;
		padding: 3px 9px;
		border-radius: 100px;
		color: var(--text-light);
		background: color-mix(in srgb, var(--text) 6%, transparent);
		white-space: nowrap;
		opacity: 0;
		animation: fade-up-in 3.4s ease-out forwards;
		animation-delay: 0.3s;
	}

	@keyframes fade-up-in {
		0%,
		28% {
			opacity: 0;
			transform: translateY(6px);
		}
		40%,
		100% {
			opacity: 1;
			transform: translateY(0);
		}
	}

	.bottom-arrows-row {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 24px;
	}

	.hub-spacer {
		width: 100px;
		flex-shrink: 0;
	}

	.bottom-arrows-row .annotate-col {
		opacity: 0;
		animation: appear-after-join 3.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
	}

	@keyframes appear-after-join {
		0%,
		54% {
			opacity: 0;
			transform: translateY(6px);
		}
		68%,
		100% {
			opacity: 1;
			transform: translateY(0);
		}
	}

	@media (max-width: 400px) {
		.join-scene {
			transform: scale(0.85);
			padding: 30px 10px;
		}
	}
</style>
