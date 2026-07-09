<script lang="ts">
	const languages = [
		{ code: 'EN', name: 'English', dir: 'ltr' },
		{ code: 'FR', name: 'Français', dir: 'ltr' },
		{ code: 'AR', name: 'العربية', dir: 'rtl' },
		{ code: 'DE', name: 'Deutsch', dir: 'ltr' }
	];
</script>

<div class="ml-mockup">
	<div class="ml-header">
		<div class="ml-post-title"></div>
		<div class="ml-lang-switcher">
			{#each languages as lang}
				<div class="lang-chip" class:active={lang.code === 'FR'}>
					<span class="lang-code">{lang.code}</span>
				</div>
			{/each}
		</div>
	</div>
	<div class="ml-body">
		{#each languages as lang}
			<div class="lang-row" class:rtl={lang.dir === 'rtl'}>
				<div class="lang-flag-code">{lang.code}</div>
				<div class="lang-lines">
					<div class="lang-line long"></div>
					<div class="lang-line med"></div>
				</div>
				<div class="lang-status" class:done={lang.code !== 'DE'}>
					{lang.code === 'DE' ? '·' : '✓'}
				</div>
			</div>
		{/each}
	</div>
	<div class="ml-footer">
		<div class="rtl-badge">RTL supported</div>
		<div class="ml-note">AI translation available</div>
	</div>
</div>

<style>
	.ml-mockup {
		border-radius: 20px;
		border: 1px solid var(--border);
		overflow: hidden;
		background: var(--background);
		box-shadow: 0 16px 48px color-mix(in srgb, var(--text) 8%, transparent);
	}

	.ml-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 16px 20px;
		border-bottom: 1px solid var(--border);
		gap: 16px;
	}

	.ml-post-title {
		flex: 1;
		height: 14px;
		border-radius: 4px;
		background: color-mix(in srgb, var(--text) 15%, transparent);
		max-width: 160px;
	}

	.ml-lang-switcher {
		display: flex;
		gap: 6px;
	}

	.lang-chip {
		padding: 4px 10px;
		border-radius: 6px;
		font-size: 12px;
		font-weight: 600;
		border: 1px solid var(--border);
		color: var(--text-light);
		background: var(--background);
	}

	.lang-chip.active {
		background: var(--accent);
		color: #fff;
		border-color: var(--accent);
	}

	.ml-body {
		padding: 12px 20px;
		display: flex;
		flex-direction: column;
		gap: 10px;
	}

	.lang-row {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 8px 0;
		border-bottom: 1px solid color-mix(in srgb, var(--border) 60%, transparent);
	}

	.lang-row.rtl .lang-lines {
		direction: rtl;
	}

	.lang-flag-code {
		font-size: 11px;
		font-weight: 700;
		color: var(--text-light);
		width: 26px;
		text-align: center;
	}

	.lang-lines {
		flex: 1;
		display: flex;
		flex-direction: column;
		gap: 5px;
	}

	.lang-line {
		height: 8px;
		border-radius: 4px;
		background: color-mix(in srgb, var(--text) 10%, transparent);
	}

	.lang-line.long {
		width: 90%;
	}

	.lang-line.med {
		width: 65%;
	}

	.lang-status {
		font-size: 13px;
		font-weight: 700;
		width: 20px;
		text-align: center;
		color: var(--text-light);
	}

	.lang-status.done {
		color: #22c55e;
	}

	.ml-footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 14px 20px;
		border-top: 1px solid var(--border);
		background: color-mix(in srgb, var(--accent) 4%, var(--background));
	}

	.rtl-badge {
		font-size: 12px;
		font-weight: 600;
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 10%, transparent);
		padding: 3px 10px;
		border-radius: 100px;
		border: 1px solid color-mix(in srgb, var(--accent) 25%, transparent);
	}

	.ml-note {
		font-size: 12px;
		color: var(--text-light);
	}
</style>
