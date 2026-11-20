<script lang="ts">
	import {
		Button,
		FormControl,
		SplitControl,
		TextInput,
		Validation,
		confirm,
		toast
	} from '@hyvor/design/components';
	import { blogStore, hostingInfoStore } from '../../../../lib/stores/blogStore';
	import { isSubdomainValid } from '../../../../lib/helper/isSubdomainValid';
	import { updateBlog } from '../../../../lib/actions/blogActions';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	let subdomain = $state($blogStore.subdomain);
	let error: string | null = $state(null);
	let saving = $state(false);

	let changed = $derived(subdomain.trim() !== $blogStore.subdomain);

	function handleInput() {
		const val = subdomain.trim();
		error = val === $blogStore.subdomain ? null : isSubdomainValid(val);
	}

	async function save() {
		const val = subdomain.trim();

		if (val === $blogStore.subdomain) {
			return;
		}

		error = isSubdomainValid(val);
		if (error) {
			return;
		}

		const confirmed = await confirm({
			title: i18n.t('console.settings.hosting.changeSubdomain'),
			content: i18n.t('console.settings.hosting.changeSubdomainContent'),
			confirmText: i18n.t('console.settings.hosting.changeSubdomain'),
			cancelText: i18n.t('console.common.cancel')
		});

		if (!confirmed) {
			return;
		}

		saving = true;
		const toastId = toast.loading(i18n.t('console.settings.hosting.subdomainChanging'));

		try {
			const res = await updateBlog({ subdomain: val }, false);
			// the console (routes and API) is addressed by the subdomain, so reload on the new one
			location.href = '/console/' + res.subdomain + '/settings/hosting';
		} catch (err: any) {
			toast.error(err.message || 'Failed to change subdomain', { id: toastId });
			saving = false;
		}
	}
</script>

<SplitControl
	column
	label={i18n.t('console.settings.hosting.subdomain')}
	caption={i18n.t('console.settings.hosting.subdomainCaption', {
		url: $hostingInfoStore.subdomain_url
	})}
>
	<div class="subdomain">
		<FormControl>
			<TextInput
				bind:value={subdomain}
				block
				state={error ? 'error' : undefined}
				disabled={saving}
				on:input={handleInput}
			/>
			{#if error}
				<Validation state="error">{error}</Validation>
			{/if}
		</FormControl>
		<Button size="small" variant="outline" disabled={saving || !changed || !!error} onclick={save}>
			{i18n.t('console.settings.hosting.changeSubdomain')}
		</Button>
	</div>
</SplitControl>

<style>
	.subdomain {
		display: flex;
		align-items: flex-start;
		gap: 8px;
	}
	.subdomain :global(.form-control) {
		flex: 1;
	}
</style>
