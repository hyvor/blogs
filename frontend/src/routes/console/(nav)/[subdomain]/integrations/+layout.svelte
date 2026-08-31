<script lang="ts">
	import { NavLink, NavLinkGroup } from '@hyvor/design/components';
	import { page } from '$app/stores';
	import { blogStore } from '../../../lib/stores/blogStore';
	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';
	import { getI18n } from '../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();
</script>

<div class="integrations">
	<div class="nav hds-box">
		<NavLinkGroup activeBackground="var(--accent-light-mid)">
			<div class="nav-title">First-party</div>

			<NavLink
				href={consoleUrlWithBlog('/integrations/hyvor-talk')}
				active={$page.url.pathname === `/console/${$blogStore.subdomain}/integrations/hyvor-talk`}
			>
				{#snippet start()}
					<img src="/img/services/hyvor-talk.svg" alt="Hyvor Talk" />
				{/snippet}
				Hyvor Talk
				{#snippet end()}
					<span class="service">{i18n.t('console.integrations.hyvorTalk.comments.label')}</span>
				{/snippet}
			</NavLink>
			<NavLink
				href={consoleUrlWithBlog('/integrations/hyvor-post')}
				active={$page.url.pathname === `/console/${$blogStore.subdomain}/integrations/hyvor-post`}
			>
				{#snippet start()}
					<img src="/img/services/hyvor-post.svg" alt="Hyvor Post" />
				{/snippet}
				Hyvor Post
				{#snippet end()}
					<span class="service">{i18n.t('console.integrations.hyvorTalk.newsletter.label')}</span>
				{/snippet}
			</NavLink>
		</NavLinkGroup>
	</div>

	<div class="content hds-box">
		{@render children?.()}
	</div>
</div>

<style>
	.integrations {
		display: flex;
		height: 100%;
	}
	.nav {
		width: 315px;
		margin-right: 15px;
		display: flex;
		flex-direction: column;
		flex-shrink: 0;
		height: 100%;
		padding: 25px 0;
	}
	.nav img {
		width: 20px;
		height: 20px;
		border-radius: 50%;
	}
	.content {
		flex: 1;
		min-width: 0;
		height: 100%;
		overflow: auto;
	}

	.nav-title {
		font-size: 10px;
		font-weight: 600;
		text-transform: uppercase;
		color: var(--text-light);
		padding: 0 32px;
		margin-bottom: 10px;
	}

	.service {
		font-size: 12px;
		color: var(--text-light);
	}

	@media (max-width: 992px) {
		.integrations {
			flex-direction: column;
		}
		.nav {
			width: 100%;
			margin-right: 0;
			margin-bottom: 20px;
		}
	}
</style>
