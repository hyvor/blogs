<script lang="ts">
	import {
		Button,
		FormControl,
		SplitControl,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import { isValidUrl } from '../../../../lib/helper/is-valid-url';
	import { updateBlog } from '../../../../lib/actions/blogActions';
	import { blogStore, hostingInfoStore, updateBlogStore } from '../../../../lib/stores/blogStore';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	let url = $state($blogStore.hosting_url ?? '');
	let error: string | null = $state(null);
	let saving = $state(false);

	let current = $derived($blogStore.hosting_url ?? '');
	let changed = $derived(url.trim().replace(/\/+$/, '') !== current);

	async function save(value: string) {
		error = null;

		if (value !== '' && !isValidUrl(value)) {
			error = i18n.t('console.settings.hosting.validation.hostingUrlInvalid');
			return;
		}

		saving = true;
		const toastId = toast.loading(i18n.t('console.settings.hosting.hostingUrlSaving'));

		try {
			// the store is updated from the response, as the URL is normalized by the server
			const res = await updateBlog({ hosting_url: value }, false);
			updateBlogStore({ hosting_url: res.hosting_url, url: res.url }, true);
			hostingInfoStore.update((info) => ({ ...info, hosting_url: res.hosting_url, url: res.url }));
			url = res.hosting_url ?? '';
			toast.success(i18n.t('console.settings.hosting.hostingUrlSaved'), { id: toastId });
		} catch (err: any) {
			toast.error(err.message || 'Failed to save hosting URL', { id: toastId });
		} finally {
			saving = false;
		}
	}
</script>

<SplitControl
	column
	label={i18n.t('console.settings.hosting.hostingUrl')}
	caption={i18n.t('console.settings.hosting.hostingUrlCaption')}
>
	<div class="hosting-url">
		<FormControl>
			<TextInput
				bind:value={url}
				placeholder="https://example.com/blog"
				block
				state={error ? 'error' : undefined}
				disabled={saving}
				on:input={() => (error = null)}
			/>
			{#if error}
				<Validation state="error">{error}</Validation>
			{/if}
		</FormControl>
		<div class="actions">
			<Button
				size="small"
				variant="outline"
				disabled={saving || !changed}
				onclick={() => save(url.trim())}
			>
				{i18n.t('console.common.save')}
			</Button>
			{#if current}
				<Button size="small" variant="invisible" disabled={saving} onclick={() => save('')}>
					{i18n.t('console.common.remove')}
				</Button>
			{/if}
		</div>
	</div>
</SplitControl>

<style>
	.hosting-url {
		display: flex;
		align-items: flex-start;
		gap: 8px;
	}
	.hosting-url :global(.form-control) {
		flex: 1;
	}
	.actions {
		display: flex;
		gap: 4px;
	}
</style>
