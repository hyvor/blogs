<script lang="ts">
	import { NavLink, NavLinkGroup } from '@hyvor/design/components';
	import { page } from '$app/stores';
	import { blogStore } from '../../../lib/stores/blogStore';
	import IconChatText from '@hyvor/icons/IconChatText';
	import IconCodeSlash from '@hyvor/icons/IconCodeSlash';
	import IconCursorText from '@hyvor/icons/IconCursorText';
	import IconDatabase from '@hyvor/icons/IconDatabase';
	import IconExclamationTriangle from '@hyvor/icons/IconExclamationTriangle';
	import IconGlobe from '@hyvor/icons/IconGlobe';
	import IconKey from '@hyvor/icons/IconKey';
	import IconLightbulb from '@hyvor/icons/IconLightbulb';
	import IconLink45deg from '@hyvor/icons/IconLink45deg';
	import IconList from '@hyvor/icons/IconList';
	import IconPeople from '@hyvor/icons/IconPeople';
	import IconRobot from '@hyvor/icons/IconRobot';
	import IconSearchHeart from '@hyvor/icons/IconSearchHeart';
	import IconSend from '@hyvor/icons/IconSend';
	import IconSignTurnSlightRight from '@hyvor/icons/IconSignTurnSlightRight';
	import IconSignpost2 from '@hyvor/icons/IconSignpost2';
	import IconTag from '@hyvor/icons/IconTag';

	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';
	import { getI18n } from '../../../lib/i18n';
	import { cant } from '../../../lib/scope.svelte';

	const i18n = getI18n();
	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	const prefix = `/console/${$blogStore.subdomain}/settings`;
</script>

<div class="settings">
	<div class="nav hds-box">
		<NavLinkGroup activeBackground="var(--accent-light-mid)">
			<NavLink
				href={consoleUrlWithBlog('/settings')}
				active={$page.url.pathname === `${prefix}`}
				disabled={cant('blog.write')}
			>
				{#snippet start()}
					<IconList />
				{/snippet}
				{i18n.t('console.settings.nav.general')}
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/settings/users')}
				active={$page.url.pathname === `${prefix}/users`}
				disabled={cant('users.read')}
			>
				{#snippet start()}
					<IconPeople />
				{/snippet}
				{i18n.t('console.settings.nav.users')}
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/settings/tags')}
				active={$page.url.pathname === `${prefix}/tags`}
				disabled={cant('tags.read')}
			>
				{#snippet start()}
					<IconTag />
				{/snippet}
				{i18n.t('console.settings.nav.tags')}
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/settings/languages')}
				active={$page.url.pathname === `${prefix}/languages`}
				disabled={cant('languages.read')}
			>
				{#snippet start()}
					<IconGlobe />
				{/snippet}
				{i18n.t('console.settings.nav.languages')}
			</NavLink>

			<div class="section-div"></div>

			<NavLink
				href={consoleUrlWithBlog('/settings/hosting')}
				active={$page.url.pathname === `${prefix}/hosting`}
				disabled={cant('blog.write')}
			>
				{#snippet start()}
					<IconDatabase />
				{/snippet}
				{i18n.t('console.settings.nav.hosting')}
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/settings/seo')}
				active={$page.url.pathname === `${prefix}/seo`}
				disabled={cant('blog.write')}
			>
				{#snippet start()}
					<IconSearchHeart />
				{/snippet}
				{i18n.t('console.posts.seo')}
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/settings/ai')}
				active={$page.url.pathname === `${prefix}/ai`}
				disabled={cant('blog.write')}
			>
				{#snippet start()}
					<IconRobot />
				{/snippet}
				AI
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/settings/color-mode')}
				active={$page.url.pathname === `${prefix}/color-mode`}
				disabled={cant('blog.write')}
			>
				{#snippet start()}
					<IconLightbulb />
				{/snippet}
				{i18n.t('console.settings.nav.colorMode')}
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/settings/post-content')}
				active={$page.url.pathname === `${prefix}/post-content`}
				disabled={cant('blog.write')}
			>
				{#snippet start()}
					<IconCursorText />
				{/snippet}
				{i18n.t('console.tools.import.postContent')}
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/settings/navigation')}
				active={$page.url.pathname === `${prefix}/navigation`}
				disabled={cant('navigations.read')}
			>
				{#snippet start()}
					<IconLink45deg />
				{/snippet}
				{i18n.t('console.settings.nav.navigation')}
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/settings/redirects')}
				active={$page.url.pathname === `${prefix}/redirects`}
				disabled={cant('redirects.read')}
			>
				{#snippet start()}
					<IconSignTurnSlightRight />
				{/snippet}
				{i18n.t('console.settings.nav.redirects')}
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/settings/routes')}
				active={$page.url.pathname === `${prefix}/routes`}
				disabled={cant('routes.read')}
			>
				{#snippet start()}
					<IconSignpost2 />
				{/snippet}
				{i18n.t('console.settings.nav.routes')}
			</NavLink>

			<div class="section-div"></div>

			<NavLink
				href={consoleUrlWithBlog('/settings/comments')}
				active={$page.url.pathname === `${prefix}/comments`}
				disabled={cant('blog.write')}
			>
				{#snippet start()}
					<IconChatText />
				{/snippet}
				{i18n.t('console.settings.nav.comments')}
			</NavLink>
			<NavLink
				href={consoleUrlWithBlog('/settings/code')}
				active={$page.url.pathname === `${prefix}/code`}
				disabled={cant('blog.write')}
			>
				{#snippet start()}
					<IconCodeSlash />
				{/snippet}
				{i18n.t('console.settings.nav.code')}
			</NavLink>
			<NavLink
				href={consoleUrlWithBlog('/settings/api-keys')}
				active={$page.url.pathname === `${prefix}/api-keys`}
				disabled={cant('api_keys.read')}
			>
				{#snippet start()}
					<IconKey />
				{/snippet}
				{i18n.t('console.settings.nav.apiKeys')}
			</NavLink>
			<NavLink
				href={consoleUrlWithBlog('/settings/webhooks')}
				active={$page.url.pathname === `${prefix}/webhooks`}
				disabled={cant('webhooks.read')}
			>
				{#snippet start()}
					<IconSend />
				{/snippet}
				{i18n.t('console.settings.nav.webhooks')}
			</NavLink>

			<div class="section-div"></div>

			<NavLink
				href={consoleUrlWithBlog('/settings/danger')}
				active={$page.url.pathname === `${prefix}/danger`}
				disabled={cant('blog.delete')}
			>
				{#snippet start()}
					<IconExclamationTriangle />
				{/snippet}
				{i18n.t('console.settings.nav.danger')}
			</NavLink>
		</NavLinkGroup>
	</div>

	<div class="content hds-box">
		{@render children?.()}
	</div>
</div>

<style>
	.settings {
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
		overflow: auto;
	}
	.content {
		flex: 1;
		min-width: 0;
		height: 100%;
		display: flex;
		flex-direction: column;
	}
	.section-div {
		height: 25px;
		flex-shrink: 0;
	}

	@media (max-width: 992px) {
		.settings {
			flex-direction: column;
		}
		.nav {
			width: 100%;
			margin-right: 0;
			margin-bottom: 20px;
		}
	}
</style>
