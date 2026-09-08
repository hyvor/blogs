<script>
	import { Button, Tooltip, confirm, toast } from '@hyvor/design/components';
	import IconCloudDownload from '@hyvor/icons/IconCloudDownload';
	import { getI18n } from '../../../lib/i18n';
	import consoleApi from '../../../lib/consoleApi';

	const i18n = getI18n();

	async function handleDownload() {
		const confirmed = await confirm({
			title: i18n.t('console.theme.downloadConfirm.title'),
			content: i18n.t('console.theme.downloadConfirm.content'),
			confirmText: i18n.t('console.theme.downloadConfirm.confirm'),
			autoClose: false
		});

		if (!confirmed) {
			return;
		}

		confirmed.loading(true);

		try {
			const response = await consoleApi.get({
				endpoint: '/theme/download',
				raw: true
			});

			const blob = await response.blob();

			const contentDisposition = response.headers.get('Content-Disposition');
			const filenameMatch = contentDisposition?.match(/filename="?([^"]+)"?/);
			const filename = filenameMatch ? filenameMatch[1] : 'theme.zip';

			const url = URL.createObjectURL(blob);

			const a = document.createElement('a');
			a.href = url;
			a.download = filename;
			document.body.appendChild(a);
			a.click();
			a.remove();

			URL.revokeObjectURL(url);
		} catch (error) {
			console.error(error);
			toast.error(
				error instanceof Error && error.message ? error.message : 'An unexpected error occurred'
			);
		}

		confirmed.close();
	}
</script>

<Tooltip text={i18n.t('console.theme.downloadTooltip')}>
	<Button color="input" size="small" style="font-size:13px" on:click={handleDownload}>
		{#snippet start()}
			<IconCloudDownload size={16} />
		{/snippet}
		{i18n.t('console.theme.download')}
	</Button>
</Tooltip>
