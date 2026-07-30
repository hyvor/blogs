<script lang="ts">
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconChevronExpand from '@hyvor/icons/IconChevronExpand';
	import IconFiles from '@hyvor/icons/IconFiles';
	import IconGear from '@hyvor/icons/IconGear';
	import IconHouse from '@hyvor/icons/IconHouse';
	import IconPalette from '@hyvor/icons/IconPalette';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconPlugin from '@hyvor/icons/IconPlugin';
	import IconTools from '@hyvor/icons/IconTools';

	import { page } from '$app/stores';
	import { NavLink, NavLinkGroup } from '@hyvor/design/components';
	import { consoleUrl } from '../../lib/consoleUrl';
	import { blogSelectorOpenStore } from '../../lib/stores';
	import type { BlogList } from '../../lib/types';

	import IconEnvelope from '@hyvor/icons/IconEnvelope';

	import IconChat from '@hyvor/icons/IconChat';
	import { integrationsStore } from '../../lib/stores/blogStore';

	interface Props {
		listItem: BlogList;
	}

	let { listItem }: Props = $props();
</script>

<button type="button" class="current-blog" onclick={() => ($blogSelectorOpenStore = true)}>
	<div class="name-url">
		<div class="name">
			{listItem.name || 'Unnamed'}
		</div>
		<div class="url">
			{listItem.url.replace(/https?:\/\//, '')}
		</div>
	</div>

	<div class="current-icon">
		<IconChevronExpand />
	</div>
</button>

<div class="nav-items">
	<NavLinkGroup activeBackground="var(--accent-light-mid)">
		<NavLink
			href={consoleUrl(listItem.subdomain)}
			active={$page.url.pathname === `/console/${listItem.subdomain}`}
		>
			{#snippet start()}
				<IconHouse />
			{/snippet}

			Home

			{#snippet end()}
				<a class="home-link" href={listItem.url} target="_blank">
					<IconBoxArrowUpRight size={12} />
				</a>
			{/snippet}
		</NavLink>

		<div class="section-div"></div>

		<NavLink
			href={consoleUrl(`${listItem.subdomain}/posts`)}
			active={$page.url.pathname.startsWith(`/console/${listItem.subdomain}/posts`)}
		>
			{#snippet start()}
				<IconPencil />
			{/snippet}
			Posts
		</NavLink>

		<NavLink
			href={consoleUrl(`${listItem.subdomain}/pages`)}
			active={$page.url.pathname.startsWith(`/console/${listItem.subdomain}/pages`)}
		>
			{#snippet start()}
				<IconFiles />
			{/snippet}
			Pages
		</NavLink>

		{#if $integrationsStore.hyvor_talk}
			<NavLink
				href={consoleUrl(`${listItem.subdomain}/comments`)}
				active={$page.url.pathname.startsWith(`/console/${listItem.subdomain}/comments`)}
			>
				{#snippet start()}
					<IconChat />
				{/snippet}
				Comments
				{#snippet end()}
					<img
						alt="Hyvor Talk"
						class="integration-icon"
						src="/img/services/hyvor-talk.svg"
					/>
				{/snippet}
			</NavLink>
		{/if}

		{#if $integrationsStore.hyvor_post}
			<NavLink
				href={consoleUrl(`${listItem.subdomain}/newsletter`)}
				active={$page.url.pathname.startsWith(`/console/${listItem.subdomain}/newsletter`)}
			>
				{#snippet start()}
					<IconEnvelope />
				{/snippet}
				Newsletter
				{#snippet end()}
					<img
						alt="Hyvor Post"
						class="integration-icon"
						src="/img/services/hyvor-post.svg"
					/>
				{/snippet}
			</NavLink>
		{/if}

		<div class="section-div"></div>

		<NavLink
			href={consoleUrl(`${listItem.subdomain}/theme`)}
			active={$page.url.pathname === `/console/${listItem.subdomain}/theme`}
		>
			{#snippet start()}
				<IconPalette />
			{/snippet}
			Theme
		</NavLink>

		<NavLink
			href={consoleUrl(`${listItem.subdomain}/integrations`)}
			active={$page.url.pathname.startsWith(`/console/${listItem.subdomain}/integrations`)}
		>
			{#snippet start()}
				<IconPlugin />
			{/snippet}
			Integrations
		</NavLink>

		<NavLink
			href={consoleUrl(`${listItem.subdomain}/tools`)}
			active={$page.url.pathname.startsWith(`/console/${listItem.subdomain}/tools`)}
		>
			{#snippet start()}
				<IconTools />
			{/snippet}
			Tools
		</NavLink>

		<NavLink
			href={consoleUrl(`${listItem.subdomain}/settings`)}
			active={$page.url.pathname.startsWith(`/console/${listItem.subdomain}/settings`)}
		>
			{#snippet start()}
				<IconGear />
			{/snippet}
			Settings
		</NavLink>
	</NavLinkGroup>
</div>

<style lang="scss">
	.current-blog {
		display: flex;
		align-items: center;
		width: calc(100% - 20px);
		padding: 10px 20px;
		cursor: pointer;
		border-radius: var(--box-radius);
		margin: 10px;
		font-family: inherit;
		font-size: inherit;
		text-align: left;
		background: none;
		border: none;
		color: inherit;
	}

	.current-blog:hover {
		background-color: var(--hover);
	}

	.name-url {
		min-width: 0;
		overflow: hidden;
		flex: 1;
	}

	.url {
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 0.8rem;
		color: var(--text-light);
		white-space: nowrap;
	}

	.current-icon {
		margin-left: 4px;
	}

	.nav-items {
		padding-bottom: 20px;
		padding-top: 10px;
	}

	.home-link {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
		transition:
			0.2s background-color,
			0.2s box-shadow;
	}

	.home-link:hover {
		background-color: var(--accent-light);
		box-shadow: 0 0 0 6px var(--accent-light);
	}

	.section-div {
		height: 25px;
	}

	.integration-icon {
		width: 16px;
		height: 16px;
	}

	@media (max-width: 992px) {
		.current-blog {
			margin: 0;
			border-radius: 0;
		}
		.nav-items {
			padding: 0;
			display: flex;
			border-top: 1px solid var(--border);
			:global(a) {
				flex: 1;
				justify-content: center;
				padding: 15px 0;
				border-top: 3px solid transparent;
				border-left: none !important;
			}
			:global(a .start) {
				margin-right: 0 !important;
			}
			:global(a .middle) {
				display: none;
			}
			:global(a.active) {
				border-top: 3px solid var(--accent);
			}
			:global(a .end) {
				display: none;
			}
		}
	}
</style>
