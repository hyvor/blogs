<script lang="ts">
	import { NavLink, NavLinkGroup } from '@hyvor/design/components';
	import { page } from '$app/stores';
	import { blogStore } from '../../../lib/stores/blogStore';
	import IconDownload from '@hyvor/icons/IconDownload';
	import IconImage from '@hyvor/icons/IconImage';
	import IconLink45deg from '@hyvor/icons/IconLink45deg';
	import IconUpload from '@hyvor/icons/IconUpload';

	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';
	import { getI18n } from '../../../lib/i18n';

	const i18n = getI18n();
	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();
</script>

<div class="tools">
	<div class="nav hds-box">
		<NavLinkGroup activeBackground="var(--accent-light-mid)">
			<NavLink
				href={consoleUrlWithBlog('/tools/media')}
				active={$page.url.pathname === `/console/${$blogStore.subdomain}/tools/media`}
			>
				{#snippet start()}
					<IconImage />
				{/snippet}
				{i18n.t('console.tools.nav.media')}
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/tools/link-analysis')}
				active={$page.url.pathname === `/console/${$blogStore.subdomain}/tools/link-analysis`}
			>
				{#snippet start()}
					<IconLink45deg />
				{/snippet}
				{i18n.t('console.tools.nav.linkAnalysis')}
			</NavLink>

			<div class="div"></div>

			<NavLink
				href={consoleUrlWithBlog('/tools/import')}
				active={$page.url.pathname === `/console/${$blogStore.subdomain}/tools/import`}
			>
				{#snippet start()}
					<IconDownload />
				{/snippet}
				{i18n.t('console.tools.nav.import')}
			</NavLink>

			<NavLink
				href={consoleUrlWithBlog('/tools/export')}
				active={$page.url.pathname === `/console/${$blogStore.subdomain}/tools/export`}
			>
				{#snippet start()}
					<IconUpload />
				{/snippet}
				{i18n.t('console.tools.nav.export')}
			</NavLink>
		</NavLinkGroup>
	</div>

	<div class="content">
		{@render children?.()}
	</div>
</div>

<style>
	.tools {
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
	.content {
		flex: 1;
		min-width: 0;
		height: 100%;
		overflow: auto;
		display: flex;
		flex-direction: column;
	}

	.div {
		height: 25px;
	}

	@media (max-width: 992px) {
		.tools {
			flex-direction: column;
		}
		.nav {
			width: 100%;
			margin-right: 0;
			margin-bottom: 20px;
		}
	}
</style>
