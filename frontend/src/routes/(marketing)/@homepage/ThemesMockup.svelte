<div class="themes-col">
	<!-- Back mockup: dark theme -->
	<div class="theme-mockup theme-mockup-back">
		<div class="tmock-bar dark">
			<span class="dot r"></span><span class="dot y"></span><span class="dot g"></span>
			<span class="tmock-url">yourblog.com</span>
		</div>
		<div class="tmock-body dark">
			<div class="tb-header">
				<div class="tb-logo"></div>
				<div class="tb-nav"><span></span><span></span><span></span></div>
			</div>
			<div class="tb-hero"></div>
			<div class="tb-content">
				<div class="tb-tag"></div>
				<div class="tb-title wide"></div>
				<div class="tb-title narrow"></div>
				<div class="tb-meta"></div>
				<div class="tb-text"></div>
				<div class="tb-text"></div>
				<div class="tb-text short"></div>
			</div>
		</div>
	</div>

	<!-- Front mockup: light theme (blog listing, amber/orange) -->
	<div class="theme-mockup theme-mockup-front">
		<div class="tmock-bar">
			<span class="dot r"></span><span class="dot y"></span><span class="dot g"></span>
			<span class="tmock-url">yourblog.com</span>
		</div>
		<div class="tmock-body light">
			<div class="tbl-header">
				<div class="tb-logo light"></div>
				<div class="tbl-subscribe"></div>
			</div>
			<div class="tbl-featured">
				<div class="tbl-featured-img"></div>
				<div class="tbl-featured-content">
					<div class="tbl-tag"></div>
					<div class="tbl-ftitle wide"></div>
					<div class="tbl-ftitle narrow"></div>
					<div class="tbl-fmeta"></div>
				</div>
			</div>
			<div class="tbl-cards">
				<div class="tbl-card">
					<div class="tbl-card-img"></div>
					<div class="tbl-card-line wide"></div>
					<div class="tbl-card-line narrow"></div>
				</div>
				<div class="tbl-card">
					<div class="tbl-card-img"></div>
					<div class="tbl-card-line wide"></div>
					<div class="tbl-card-line narrow"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<style lang="scss">
	.themes-col {
		position: relative;
		height: 400px;
	}

	.theme-mockup {
		position: absolute;
		width: 78%;
		border-radius: 20px;
		overflow: hidden;
		border: 1px solid var(--border);
	}

	.theme-mockup-back {
		top: 0;
		right: 0;
		transform: rotate(3.5deg);
		transform-origin: top right;
		z-index: 1;
		box-shadow: 0 16px 48px rgba(0, 0, 0, 0.3);
	}

	.theme-mockup-front {
		bottom: 0;
		left: 0;
		transform: rotate(-2deg);
		transform-origin: bottom left;
		z-index: 2;
		box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
	}

	.dot {
		display: block;
		width: 11px;
		height: 11px;
		border-radius: 50%;
		&.r {
			background: #ff5f57;
		}
		&.y {
			background: #febc2e;
		}
		&.g {
			background: #28c840;
		}
	}

	.tmock-bar {
		display: flex;
		align-items: center;
		gap: 6px;
		padding: 9px 12px;
		background: #f1f1f3;
		border-bottom: 1px solid #e5e7eb;

		&.dark {
			background: #1a1a2e;
			border-bottom-color: rgba(255, 255, 255, 0.06);
		}
	}

	.tmock-url {
		flex: 1;
		text-align: center;
		font-size: 10px;
		padding: 2px 8px;
		border-radius: 4px;
		max-width: 130px;
		margin: 0 auto;
		background: white;
		color: #6b7280;
		border: 1px solid #e5e7eb;

		.dark & {
			background: rgba(255, 255, 255, 0.06);
			color: rgba(255, 255, 255, 0.3);
			border-color: rgba(255, 255, 255, 0.08);
		}
	}

	.tmock-body {
		&.light {
			background: #ffffff;
		}
		&.dark {
			background: #0d0d1a;
		}
	}

	.tb-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 11px 14px;

		.light & {
			border-bottom: 1px solid #f3f4f6;
		}
		.dark & {
			border-bottom: 1px solid rgba(255, 255, 255, 0.05);
		}
	}

	.tb-logo {
		width: 56px;
		height: 9px;
		border-radius: 3px;
		.light & {
			background: #1f2937;
		}
		.dark & {
			background: rgba(255, 255, 255, 0.65);
		}
		&.light {
			background: var(--accent);
			opacity: 0.5;
		}
	}

	.tb-nav {
		display: flex;
		gap: 8px;
		span {
			display: block;
			width: 28px;
			height: 7px;
			border-radius: 3px;
		}
		.light & span {
			background: #d1d5db;
		}
		.dark & span {
			background: rgba(255, 255, 255, 0.12);
		}
	}

	.tb-hero {
		height: 90px;
		.light & {
			background: linear-gradient(135deg, #6366f1, #8b5cf6);
		}
		.dark & {
			background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 100%);
		}
	}

	.tb-content {
		padding: 13px 14px 16px;
	}

	.tb-tag {
		width: 48px;
		height: 14px;
		border-radius: 100px;
		margin-bottom: 9px;
		.light & {
			background: #e0e7ff;
		}
		.dark & {
			background: rgba(99, 102, 241, 0.3);
		}
	}

	.tb-title {
		height: 11px;
		border-radius: 3px;
		margin-bottom: 6px;
		&.wide {
			width: 90%;
		}
		&.narrow {
			width: 68%;
		}
		.light & {
			background: #1f2937;
		}
		.dark & {
			background: rgba(255, 255, 255, 0.7);
		}
	}

	.tb-meta {
		width: 90px;
		height: 7px;
		border-radius: 3px;
		margin-bottom: 11px;
		.light & {
			background: #d1d5db;
		}
		.dark & {
			background: rgba(255, 255, 255, 0.18);
		}
	}

	.tb-text {
		height: 7px;
		border-radius: 3px;
		margin-bottom: 7px;
		width: 100%;
		&.short {
			width: 62%;
		}
		.light & {
			background: #e5e7eb;
		}
		.dark & {
			background: rgba(255, 255, 255, 0.09);
		}
	}

	.tbl-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 10px 14px;
		border-bottom: 1px solid var(--border);
		background: color-mix(in srgb, var(--accent) 4%, var(--background));
	}

	.tbl-subscribe {
		width: 52px;
		height: 20px;
		border-radius: 100px;
		background: var(--accent);
		opacity: 0.5;
	}

	.tbl-featured {
		display: flex;
		gap: 10px;
		padding: 12px 14px 10px;
		border-bottom: 1px solid var(--border);
	}

	.tbl-featured-img {
		width: 72px;
		height: 60px;
		border-radius: 8px;
		flex-shrink: 0;
		background: var(--accent-light);
	}

	.tbl-featured-content {
		flex: 1;
		min-width: 0;
		display: flex;
		flex-direction: column;
		gap: 5px;
		padding-top: 2px;
	}

	.tbl-tag {
		width: 40px;
		height: 12px;
		border-radius: 100px;
		background: var(--accent-light);
	}

	.tbl-ftitle {
		height: 10px;
		border-radius: 3px;
		background: color-mix(in srgb, var(--text-light) 45%, transparent);
		&.wide {
			width: 95%;
		}
		&.narrow {
			width: 75%;
		}
	}

	.tbl-fmeta {
		width: 80px;
		height: 7px;
		border-radius: 3px;
		background: color-mix(in srgb, var(--text-light) 25%, transparent);
		margin-top: 2px;
	}

	.tbl-cards {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 8px;
		padding: 10px 14px 12px;
		background: color-mix(in srgb, var(--accent) 4%, var(--background));
	}

	.tbl-card {
		display: flex;
		flex-direction: column;
		gap: 6px;
	}

	.tbl-card-img {
		height: 40px;
		border-radius: 6px;
		background: var(--accent-light);
		opacity: 0.9;
	}

	.tbl-card-line {
		height: 7px;
		border-radius: 3px;
		background: color-mix(in srgb, var(--text-light) 45%, transparent);
		&.wide {
			width: 90%;
		}
		&.narrow {
			width: 65%;
		}
	}

	@media (max-width: 900px) {
		.themes-col {
			height: 340px;
			width: 100%;
		}
	}
</style>
