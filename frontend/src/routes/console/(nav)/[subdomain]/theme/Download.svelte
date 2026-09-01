<script>
	import { Button, Tooltip, confirm } from '@hyvor/design/components';
	import IconCloudDownload from '@hyvor/icons/IconCloudDownload';
	import { blogStore } from '../../../lib/stores/blogStore';
	import { getI18n } from '../../../lib/i18n';

	const i18n = getI18n();

	async function handleDownload() {
		if (
			await confirm({
				title: i18n.t('console.theme.downloadConfirm.title'),
				content: i18n.t('console.theme.downloadConfirm.content'),
				confirmText: i18n.t('console.theme.downloadConfirm.confirm')
			})
		) {
			const url = '/api/console/v0/blog/' + $blogStore.subdomain + '/theme/download';

			window.open(url, '_blank');
		}
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
