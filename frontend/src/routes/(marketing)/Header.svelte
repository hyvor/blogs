<script lang="ts">
	import { Header } from '@hyvor/design/marketing';
	import { Button, Dropdown } from '@hyvor/design/components';
	import { page } from '$app/stores';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';

	let resourcesDropdown = $state(false);

	function handleResourceButtonsClick(e: MouseEvent) {
		const target = e.target as HTMLElement;
		if (target.tagName === 'A' || target.closest('a.button')) {
			resourcesDropdown = false;
		}
	}
</script>

<Header product="blogs" subName="Blogs" darkToggle={false}>
	{#snippet center()}
		<div class="center">
			<Button
				as="a"
				size="small"
				href="/pricing"
				variant={$page.url.pathname === '/pricing' ? 'fill-light' : 'invisible'}
			>
				Pricing
			</Button>
			<Button
				as="a"
				size="small"
				href="/docs"
				variant={$page.url.pathname.startsWith('/docs') ? 'fill-light' : 'invisible'}
			>
				Docs
			</Button>
			<Dropdown bind:show={resourcesDropdown}>
				{#snippet trigger()}
					<Button variant="invisible" size="small">
						Resources
						{#snippet end()}
							<IconCaretDown size={12} />
						{/snippet}
					</Button>
				{/snippet}
				{#snippet content()}
					<!-- svelte-ignore a11y_click_events_have_key_events, a11y_no_static_element_interactions -->
					<div class="resource-buttons" onclick={handleResourceButtonsClick}>
						<Button
							as="a"
							size="small"
							href="/themes"
							variant={$page.url.pathname === '/themes' ? 'fill-light' : 'invisible'}
							block
						>
							Themes
						</Button>

						<Button
							as="a"
							size="small"
							href="/integrations"
							variant={$page.url.pathname.startsWith('/integrations')
								? 'fill-light'
								: 'invisible'}
							block
						>
							Integrations
						</Button>
					</div>
				{/snippet}
			</Dropdown>
		</div>
	{/snippet}

	{#snippet end()}
		<div class="end">
			<Button as="a" size="small" href="/console">Go to Console &rarr;</Button>
		</div>
	{/snippet}
</Header>

<style>
	.end {
		display: flex;
		align-items: center;
		gap: 5px;
	}

	.resource-buttons {
		display: flex;
		flex-direction: column;
		gap: 5px;
		padding: 5px;
	}

	.resource-buttons :global(.button-content) {
		justify-content: flex-start;
	}

	/* mobile styles */
	@media (max-width: 768px) {
		.center {
			display: flex;
			flex-direction: column;
		}

		.center {
			display: flex;
			flex-direction: column;
			gap: 5px;
		}
	}

	@media (max-width: 992px) {
		.center {
			display: flex;
			flex-direction: column;
			gap: 5px;
		}

		.end {
			flex-direction: column;
			gap: 5px;
			align-items: center;
		}
	}
</style>
