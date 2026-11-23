<script lang="ts">
	import { Button, Checkbox } from '@hyvor/design/components';
	import { SCOPES, type Scope } from '../../../../lib/types';
	import { can } from '../../../../lib/scope.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	const GROUP_KEYS = {
		blog: 'console.settings.apiKeys.scopeGroups.blog',
		posts: 'console.settings.apiKeys.scopeGroups.posts',
		users: 'console.settings.apiKeys.scopeGroups.users',
		tags: 'console.settings.apiKeys.scopeGroups.tags',
		languages: 'console.settings.apiKeys.scopeGroups.languages',
		media: 'console.settings.apiKeys.scopeGroups.media',
		navigations: 'console.settings.apiKeys.scopeGroups.navigations',
		routes: 'console.settings.apiKeys.scopeGroups.routes',
		redirects: 'console.settings.apiKeys.scopeGroups.redirects',
		webhooks: 'console.settings.apiKeys.scopeGroups.webhooks',
		api_keys: 'console.settings.apiKeys.scopeGroups.api_keys',
		themes: 'console.settings.apiKeys.scopeGroups.themes',
		import: 'console.settings.apiKeys.scopeGroups.import',
		export: 'console.settings.apiKeys.scopeGroups.export',
		link_analysis: 'console.settings.apiKeys.scopeGroups.link_analysis',
		integrations: 'console.settings.apiKeys.scopeGroups.integrations',
		ai: 'console.settings.apiKeys.scopeGroups.ai'
	} as const;

	type Group = keyof typeof GROUP_KEYS;

	interface Props {
		selected: Scope[];
	}

	let { selected = $bindable() }: Props = $props();

	// only scopes the current user (or API key) has can be granted
	const available = SCOPES.filter((scope) => can(scope));

	const groups = (() => {
		const map = new Map<Group, { scope: Scope; action: string }[]>();
		for (const scope of available) {
			const dot = scope.indexOf('.');
			const group = scope.slice(0, dot) as Group;
			const action = scope.slice(dot + 1);
			if (!map.has(group)) map.set(group, []);
			map.get(group)!.push({ scope, action });
		}
		return [...map.entries()];
	})();

	let allSelected = $derived(available.every((scope) => selected.includes(scope)));
	let noneSelected = $derived(selected.length === 0);

	function toggle(scope: Scope, checked: boolean) {
		if (checked) {
			if (!selected.includes(scope)) selected = [...selected, scope];
		} else {
			selected = selected.filter((s) => s !== scope);
		}
	}
</script>

<div class="scope-picker">
	<div class="actions">
		<Button
			size="small"
			color="input"
			disabled={allSelected}
			on:click={() => (selected = [...available])}
		>
			{i18n.t('console.settings.apiKeys.selectAll')}
		</Button>
		<Button size="small" color="input" disabled={noneSelected} on:click={() => (selected = [])}>
			{i18n.t('console.settings.apiKeys.deselectAll')}
		</Button>
	</div>

	<div class="groups">
		{#each groups as [group, scopes] (group)}
			<div class="group">
				<div class="group-name">{i18n.t(GROUP_KEYS[group])}</div>
				<div class="group-scopes">
					{#each scopes as { scope, action } (scope)}
						<Checkbox
							checked={selected.includes(scope)}
							on:change={(e) => toggle(scope, (e.target as HTMLInputElement).checked)}
						>
							<span title={scope}>{action}</span>
						</Checkbox>
					{/each}
				</div>
			</div>
		{/each}
	</div>
</div>

<style>
	.actions {
		display: flex;
		gap: 8px;
		margin-bottom: 12px;
	}

	.groups {
		display: flex;
		flex-direction: column;
		gap: 10px;
	}

	.group {
		display: grid;
		grid-template-columns: 120px 1fr;
		align-items: start;
		gap: 12px;
	}

	.group-name {
		font-weight: 600;
		font-size: 14px;
		padding-top: 2px;
	}

	.group-scopes {
		display: flex;
		flex-wrap: wrap;
		gap: 6px 18px;
	}
</style>
