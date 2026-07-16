<script lang="ts">
	import { run } from 'svelte/legacy';

	import { createEventDispatcher, onMount } from 'svelte';
	import type { Theme } from '../../console/lib/types';
	import { loadThemes } from '../../console/(nav)/[subdomain]/theme/themeActions';
	import { loadConfig } from '../../console/lib/config';
	import {
		ActionList,
		ActionListGroup,
		ActionListItem,
		Button,
		Dropdown,
		IconButton,
		IconMessage,
		Link,
		Loader,
		Text
	} from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import IconLaptop from '@hyvor/icons/IconLaptop';
	import IconLock from '@hyvor/icons/IconLock';
	import IconTablet from '@hyvor/icons/IconTablet';
	import IconGithub from '@hyvor/icons/IconGithub';

	interface Props {
		lockScroll?: boolean;
	}

	let { lockScroll = $bindable(false) }: Props = $props();

	let isLoaded = $state(false);
	let themes: Theme[] = $state([]);

	let port: string = $state('');
	let type: 'laptop' | 'tablet' = $state('laptop');

	let isLoading = $state(true);
	let dropdownOpen = $state(false);

	const dispatch = createEventDispatcher();

	onMount(async () => {
		await loadConfig();
		themes = await loadThemes();
		isLoaded = true;

		dispatch('load');

		// support local
		port = window.location.port ? `:${Number(window.location.port) + 1}` : '';
	});

	function selectTheme(theme: Theme) {
		isLoading = true;
		currentTheme = theme;
		dropdownOpen = false;
	}

	let originalThemes = $derived(
		themes.filter((theme) => theme.type === 'original' && theme.name !== 'blank')
	);
	let portedThemes = $derived(themes.filter((theme) => theme.type === 'ported'));
	let currentTheme: any = $state(null);
	run(() => {
		currentTheme = originalThemes[0];
	});
	// TODO: revert to getConfig().domains?.delivery once /api/special/config exists in the Symfony backend
	const deliveryDomain = 'hyvorblogs.localhost';

	let currentThemeUrl = $derived(
		`http://${currentTheme?.preview_subdomain}.${deliveryDomain}${port}`
	);
</script>

{#if isLoaded}
	<div class="preview hds-box">
		<div class="navi">
			<Dropdown bind:show={dropdownOpen} width={220}>
				{#snippet trigger()}
					<Button color="input">
						{#snippet start()}
							<Text bold>Theme</Text>
						{/snippet}

						<span class="theme-name">{currentTheme?.name}</span>

						{#snippet end()}
							<IconCaretDown size={14} />
						{/snippet}
					</Button>
				{/snippet}

				{#snippet content()}
					<ActionList>
						{#each [originalThemes, portedThemes] as group, i}
							<ActionListGroup title={i === 0 ? 'Original' : 'Ported'} divider={i > 0}>
								{#each group as theme (theme.name)}
									{#if theme.name !== 'blank'}
										<ActionListItem
											on:select={() => selectTheme(theme)}
											style={currentTheme?.name === theme.name
												? 'background-color: var(--accent-light-mid)'
												: ''}
										>
											<span class="theme-item-name">{theme.name}</span>
										</ActionListItem>
									{/if}
								{/each}
							</ActionListGroup>
						{/each}
					</ActionList>

					<div class="open-source">
						<Text small>Themes are open-source</Text>
						<Button
							size="small"
							as="a"
							href="https://github.com/hyvor/hyvor-blogs-themes"
							target="_blank"
						>
							View Source
							{#snippet end()}
								<IconGithub size={14} />
							{/snippet}
						</Button>
					</div>
				{/snippet}
			</Dropdown>

			<div class="right">
				<Link href={currentThemeUrl} target="_blank" underline={false} color="text">
					Open in new tab
					{#snippet end()}
						<IconBoxArrowUpRight size={14} />
					{/snippet}
				</Link>

				<div class="device-toggle">
					<IconButton
						on:click={() => (type = 'laptop')}
						variant={type == 'laptop' ? 'fill' : 'invisible'}><IconLaptop /></IconButton
					>

					<IconButton
						on:click={() => (type = 'tablet')}
						variant={type == 'tablet' ? 'fill' : 'invisible'}><IconTablet /></IconButton
					>
				</div>
			</div>
		</div>

		{#if currentTheme}
			<div class="iframe" style="padding: {type === 'laptop' ? 0 : 15}px">
				{#if isLoading}
					<Loader full />
				{/if}

				<iframe
					src={currentThemeUrl}
					title={currentTheme.name}
					style:width={type === 'laptop' ? '100%' : (type === 'tablet' ? 540 : 360) + 'px'}
					style:height={type === 'laptop' ? '100%' : 740 + 'px'}
					onload={() => (isLoading = false)}
					style:display={isLoading ? 'none' : 'block'}
				></iframe>

				{#if lockScroll}
					<button class="lock-scroll" onclick={() => (lockScroll = false)}>
						<div class="overlay"></div>
						<IconMessage icon={IconLock} iconSize={50} message="Click to unlock scroll" />
					</button>
				{/if}
			</div>
		{/if}
	</div>
{/if}

<style lang="scss">
	.preview {
		width: 100%;
		height: 100%;
		display: flex;
		justify-content: center;
		overflow: hidden;
		flex-direction: column;
	}

	.navi {
		padding: 12px 20px;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
		border-bottom: 1px solid var(--border);
	}

	.theme-name {
		text-transform: capitalize;
	}

	.theme-item-name {
		text-transform: capitalize;
	}

	/* the group's own margin-top plus the divider's margin-bottom stack up
	   into a double-gap around a single line; tighten so it reads as one */
	:global(.action-list-group.has-divider) {
		margin-top: 4px !important;
	}
	:global(.action-list-group .divider) {
		margin-bottom: 10px;
	}

	.open-source {
		margin-top: 10px;
		padding: 10px 4px 0;
		border-top: 1px solid var(--border);
		display: flex;
		flex-direction: column;
		align-items: center;
		text-align: center;
		gap: 8px;
	}

	.right {
		display: flex;
		align-items: center;
		gap: 20px;
		font-size: 14px;
		font-weight: 600;
	}

	.iframe {
		flex: 1;
		display: flex;
		align-items: center;
		justify-content: center;
		overflow: hidden;
		position: relative;
	}

	.lock-scroll {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		z-index: 1;
		cursor: pointer;
		.overlay {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background-color: #fafafa;
			opacity: 0.7;
			z-index: -1;
		}
	}

	iframe {
		max-width: 100%;
		max-height: 100%;
		border: none;
		transition:
			0.3s width,
			0.3s height;
	}

	@keyframes preview-iframe {
		0% {
			opacity: 0;
		}
		100% {
			opacity: 1;
		}
	}

	@media screen and (max-width: 992px) {
		.preview {
			height: 600px;
		}
		.right :global(a) {
			display: none;
		}
		.device-toggle {
			display: none;
		}
	}
</style>
