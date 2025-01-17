<script>
	import { Button, Tooltip, confirm } from '@hyvor/design/components';
	import { IconCloudDownload } from '@hyvor/icons';
	import { blogStore } from '../../../lib/stores/blogStore';

	async function handleDownload() {
		if (
			await confirm({
				title: 'Download theme',
				content:
					'Your theme files will be downloaded as a zip file. You can upload it to another blog.',
				confirmText: 'Yes, download'
			})
		) {
			const url = '/api/console/v0/blog/' + $blogStore.subdomain + '/theme/download';

			window.open(url, '_blank');
		}
	}
</script>

<Tooltip text="Download your theme files">
	<Button color="input" size="small" style="font-size:13px" on:click={handleDownload}>
		<IconCloudDownload size={16} slot="start" />
		Download
	</Button>
</Tooltip>
