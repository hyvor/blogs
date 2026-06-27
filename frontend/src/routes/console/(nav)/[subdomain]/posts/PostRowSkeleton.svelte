<div class="post-list-item">
	<div class="post-main">
		<div class="post-title-row">
			<div class="skeleton title"></div>
		</div>
		<div class="skeleton slug"></div>
		<div class="skeleton date"></div>
		<div class="post-languages">
			<div class="skeleton tag"></div>
			<div class="skeleton tag"></div>
		</div>
	</div>

	<div class="post-authors-tags">
		<div class="skeleton author"></div>
		<div class="post-tags">
			<div class="skeleton tag"></div>
			<div class="skeleton tag"></div>
		</div>
	</div>

	<div class="post-health-wrap">
		<div class="skeleton health"></div>
		<div class="skeleton health"></div>
	</div>

	<div class="post-actions-wrap">
		<div class="skeleton actions"></div>
	</div>
</div>

<style lang="scss">
	.post-list-item {
		display: grid;
		grid-template-columns: minmax(280px, 1.8fr) minmax(200px, 1.9fr) 100px 36px;
		gap: 14px;
		padding: 16px 30px;
		border-bottom: 1px solid var(--border);
	}

	.post-main {
		display: flex;
		flex-direction: column;
		gap: 8px;
		min-width: 0;
	}

	.post-title-row {
		display: flex;
		align-items: center;
	}

	.post-languages {
		display: flex;
		gap: 5px;
	}

	.post-authors-tags {
		display: flex;
		flex-direction: column;
		gap: 8px;
		min-width: 0;
	}

	.post-tags {
		display: flex;
		gap: 5px;
	}

	.post-health-wrap {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	.post-actions-wrap {
		display: flex;
		align-items: center;
	}

	.skeleton {
		border-radius: 4px;
		background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
		background-size: 400% 100%;
		animation: shimmer 1.8s ease-in-out infinite;
	}

	.skeleton.title {
		width: 70%;
		height: 16px;
	}

	.skeleton.slug {
		width: 40%;
		height: 12px;
	}

	.skeleton.date {
		width: 50%;
		height: 12px;
	}

	.skeleton.tag {
		width: 60px;
		height: 18px;
		border-radius: 10px;
	}

	.skeleton.author {
		width: 80px;
		height: 18px;
		border-radius: 10px;
	}

	.skeleton.health {
		width: 36px;
		height: 28px;
	}

	.skeleton.actions {
		width: 24px;
		height: 24px;
		border-radius: 4px;
	}

	@keyframes shimmer {
		0% {
			background-position: 100% 50%;
		}
		100% {
			background-position: 0 50%;
		}
	}

	@media (max-width: 992px) {
		.post-list-item {
			display: flex;
			flex-direction: column;
			align-items: stretch;
			gap: 10px;
		}
	}
</style>
