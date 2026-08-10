<script lang="ts">
	const products = [
		{ name: 'Hyvor Talk', component: 'talk', color: '#ffd969', tag: 'Complimentary license', desc: 'Comments' },
		{
			name: 'Hyvor Post',
			component: 'post',
			color: '#5a8387',
			tag: 'Complimentary license',
			desc: 'Newsletters'
		}
	];
</script>

<div class="suite-mockup">
	<div class="equation">
		{#each products as p, i}
			{#if i > 0}
				<span class="plus">+</span>
			{/if}
			<div class="product-tile">
				<div
					class="icon-wrap"
					style:background={`color-mix(in srgb, ${p.color} 20%, transparent)`}
				>
					<img
						src="https://hyvor.com/api/public/logo/{p.component}.svg"
						alt={p.name}
						width="48"
						height="48"
					/>
				</div>
				<div class="p-name">{p.name}</div>
				{#if p.desc}
					<div class="p-desc">{p.desc}</div>
				{/if}
				<span class="p-tag tag-free">{p.tag}</span>
			</div>
		{/each}
	</div>
</div>

<style>
	.suite-mockup {
		border-radius: 20px;
		/* border: 0.5px solid var(--border); */
		overflow: hidden;
		/* background: var(--background); */
		/* box-shadow: 0 16px 48px color-mix(in srgb, var(--text) 8%, transparent); */
	}

	.equation {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 22px;
		padding: 56px 32px;
	}

	.plus {
		font-size: 30px;
		font-weight: 300;
		color: var(--text-light);
		flex-shrink: 0;
	}

	.product-tile {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 10px;
		width: 170px;
	}

	.icon-wrap {
		width: 88px;
		height: 88px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.p-name {
		font-size: 16px;
		font-weight: 700;
		text-align: center;
		line-height: 1.3;
	}

	.p-desc {
		font-size: 13px;
		color: var(--text-light);
		margin-top: -6px;
	}

	.p-tag {
		font-size: 11px;
		font-weight: 600;
		padding: 4px 12px;
		border-radius: 100px;
		color: var(--text-light);
		background: color-mix(in srgb, var(--text) 6%, transparent);
		white-space: nowrap;
	}

	.p-tag.tag-free {
		color: #16a34a;
		background: color-mix(in srgb, #16a34a 14%, transparent);
	}

	@media (max-width: 480px) {
		.equation {
			gap: 12px;
			padding: 36px 16px;
		}

		.product-tile {
			width: 128px;
		}

		.icon-wrap {
			width: 68px;
			height: 68px;
		}

		.p-name {
			font-size: 13px;
		}

		.p-tag {
			font-size: 10px;
			padding: 3px 9px;
		}
	}
</style>
