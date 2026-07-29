<script lang="ts">
	import IconHandThumbsUp from '@hyvor/icons/IconHandThumbsUp';
	import IconSend from '@hyvor/icons/IconSend';
	import ProductMascot from './ProductMascot.svelte';

	const TALK_COLOR = '#ffd969';
	const POST_COLOR = '#5a8387';

	const comments = [
		{
			name: 'Isaac',
			time: '2d ago',
			likes: 12,
			text: 'This is exactly what I needed, thank you!'
		},
		{
			name: 'Maria',
			time: '5d ago',
			likes: 4,
			text: 'Trying this out on my blog today.'
		}
	];
</script>

<div class="suite-mockup-wrap">
	<div
		class="stack-card stack-talk"
		style:background={`color-mix(in srgb, ${TALK_COLOR} 30%, var(--background))`}
	>
		<ProductMascot color={TALK_COLOR} size={22} />
	</div>
	<div
		class="stack-card stack-post"
		style:background={`color-mix(in srgb, ${POST_COLOR} 30%, var(--background))`}
	>
		<ProductMascot color={POST_COLOR} size={22} />
	</div>

	<div class="free-sticker">
		<span>3 tools</span>
		<strong>1 free plan</strong>
	</div>

	<div class="suite-mockup">
	<div class="post">
		<div class="post-image"></div>
		<h4 class="post-title">Why We Moved to a Headless CMS</h4>
		<div class="post-meta">By Sarah Chen &middot; Jul 15, 2026 &middot; 4 min read</div>
		<p class="post-text">
			We spent the last few months rethinking how content flows through our stack. Here's what
			changed, and why it made publishing so much faster for our team.
		</p>
	</div>

	<div class="newsletter">
		<div class="mascot-icon" style:background={`color-mix(in srgb, ${POST_COLOR} 20%, transparent)`}>
			<ProductMascot color={POST_COLOR} size={20} />
		</div>
		<div class="newsletter-text">
			<div class="nl-title">Never miss a post</div>
			<div class="nl-subtitle">Join 1,204 readers getting new articles by email.</div>
		</div>
		<span class="badge">
			<ProductMascot color={POST_COLOR} size={14} />
			Hyvor Post
		</span>
	</div>
	<div class="newsletter-form">
		<div class="email-input">you@email.com</div>
		<div class="subscribe-btn">Subscribe</div>
	</div>

	<div class="comments">
		<div class="comments-head">
			<span class="ch-title">12 Comments</span>
			<span class="badge">
				<ProductMascot color={TALK_COLOR} size={14} />
				Hyvor Talk
			</span>
		</div>

		<div class="write-comment">
			<div class="avatar you">Y</div>
			<div class="write-input">Write a comment&hellip;</div>
			<div class="send-btn"><IconSend size={14} /></div>
		</div>

		{#each comments as c}
			<div class="comment-row">
				<div class="avatar">{c.name[0]}</div>
				<div class="comment-body">
					<div class="comment-meta">
						<span class="c-name">{c.name}</span>
						<span class="c-time">{c.time}</span>
					</div>
					<p class="c-text">{c.text}</p>
					<div class="comment-actions">
						<span class="c-action"><IconHandThumbsUp size={11} />{c.likes}</span>
						<span class="c-action">Reply</span>
					</div>
				</div>
			</div>
		{/each}
	</div>
	</div>
</div>

<style>
	.suite-mockup-wrap {
		position: relative;
		padding: 22px 18px;
	}

	.stack-card {
		position: absolute;
		top: 22px;
		bottom: 22px;
		width: 92%;
		border-radius: 20px;
		z-index: 0;
		display: flex;
		align-items: flex-end;
		justify-content: flex-end;
		padding: 18px;
	}

	.stack-talk {
		left: 4px;
		transform: rotate(-5deg);
	}

	.stack-post {
		right: 4px;
		transform: rotate(5deg);
	}

	.free-sticker {
		position: absolute;
		top: 2px;
		right: 8px;
		z-index: 2;
		display: flex;
		flex-direction: column;
		align-items: center;
		line-height: 1.25;
		padding: 8px 14px;
		border-radius: 14px;
		background: var(--accent);
		color: #fff;
		transform: rotate(6deg);
		box-shadow: 0 10px 24px color-mix(in srgb, var(--accent) 45%, transparent);
	}

	.free-sticker span {
		font-size: 9px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		opacity: 0.85;
	}

	.free-sticker strong {
		font-size: 14px;
		white-space: nowrap;
	}

	.suite-mockup {
		position: relative;
		z-index: 1;
		border-radius: 20px;
		border: 1px solid var(--border);
		overflow: hidden;
		background: var(--background);
		box-shadow: 0 16px 48px color-mix(in srgb, var(--text) 8%, transparent);
		font-size: 13px;
	}

	.post {
		padding: 20px 20px 16px;
	}

	.post-image {
		width: 100%;
		height: 96px;
		border-radius: 10px;
		margin-bottom: 14px;
		background: linear-gradient(
			135deg,
			color-mix(in srgb, var(--accent) 30%, transparent),
			color-mix(in srgb, var(--accent) 8%, transparent)
		);
	}

	.post-title {
		font-size: 17px;
		font-weight: 700;
		line-height: 1.3;
		margin: 0 0 6px;
	}

	.post-meta {
		font-size: 12px;
		color: var(--text-light);
		margin-bottom: 12px;
	}

	.post-text {
		font-size: 13px;
		line-height: 1.6;
		color: var(--text-light);
		margin: 0;
	}

	.newsletter {
		position: relative;
		display: flex;
		align-items: flex-start;
		gap: 12px;
		padding: 16px 20px 12px;
		border-top: 1px solid var(--border);
		background: color-mix(in srgb, var(--accent) 4%, var(--background));
	}

	.mascot-icon {
		flex-shrink: 0;
		width: 36px;
		height: 36px;
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 10px;
	}

	.newsletter-text {
		flex: 1;
		min-width: 0;
	}

	.nl-title {
		font-size: 14px;
		font-weight: 700;
		margin-bottom: 3px;
	}

	.nl-subtitle {
		font-size: 12px;
		color: var(--text-light);
	}

	.newsletter-form {
		display: flex;
		gap: 8px;
		padding: 0 20px 16px;
		border-bottom: 1px solid var(--border);
		background: color-mix(in srgb, var(--accent) 4%, var(--background));
	}

	.email-input {
		flex: 1;
		font-size: 12px;
		color: var(--text-light);
		padding: 9px 14px;
		border-radius: 100px;
		border: 1px solid var(--border);
		background: var(--background);
	}

	.subscribe-btn {
		flex-shrink: 0;
		font-size: 12px;
		font-weight: 600;
		color: #fff;
		background: var(--accent);
		padding: 9px 16px;
		border-radius: 100px;
		white-space: nowrap;
	}

	.comments {
		padding: 16px 20px 20px;
		display: flex;
		flex-direction: column;
		gap: 16px;
	}

	.comments-head {
		display: flex;
		align-items: center;
		justify-content: space-between;
	}

	.ch-title {
		font-size: 13px;
		font-weight: 700;
	}

	.badge {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		font-size: 11px;
		font-weight: 600;
		color: var(--text);
		background: color-mix(in srgb, var(--text) 6%, transparent);
		padding: 4px 10px 4px 6px;
		border-radius: 100px;
		white-space: nowrap;
		flex-shrink: 0;
	}

	.badge :global(svg) {
		flex-shrink: 0;
		border-radius: 50%;
	}

	.write-comment {
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.write-input {
		flex: 1;
		font-size: 12px;
		color: var(--text-light);
		padding: 9px 14px;
		border-radius: 100px;
		border: 1px solid var(--border);
	}

	.send-btn {
		flex-shrink: 0;
		width: 30px;
		height: 30px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		background: color-mix(in srgb, var(--accent) 12%, transparent);
		color: var(--accent);
	}

	.comment-row {
		display: flex;
		align-items: flex-start;
		gap: 10px;
	}

	.avatar {
		flex-shrink: 0;
		width: 28px;
		height: 28px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 12px;
		font-weight: 700;
		color: var(--text-light);
		background: color-mix(in srgb, var(--text) 10%, transparent);
	}

	.avatar.you {
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 14%, transparent);
	}

	.comment-body {
		flex: 1;
		display: flex;
		flex-direction: column;
		gap: 5px;
	}

	.comment-meta {
		display: flex;
		align-items: baseline;
		gap: 8px;
	}

	.c-name {
		font-size: 13px;
		font-weight: 700;
	}

	.c-time {
		font-size: 11px;
		color: var(--text-light);
	}

	.c-text {
		font-size: 12.5px;
		line-height: 1.5;
		color: var(--text-light);
		margin: 0;
	}

	.comment-actions {
		display: flex;
		align-items: center;
		gap: 14px;
		margin-top: 2px;
	}

	.c-action {
		display: inline-flex;
		align-items: center;
		gap: 4px;
		font-size: 11px;
		color: var(--text-light);
		font-weight: 600;
	}

	.c-action :global(svg) {
		opacity: 0.7;
	}
</style>
