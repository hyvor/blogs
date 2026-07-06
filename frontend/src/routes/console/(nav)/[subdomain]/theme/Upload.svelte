<script lang="ts">
	import { Button, Tooltip, toast } from '@hyvor/design/components';
	import IconCloudUpload from '@hyvor/icons/IconCloudUpload';
	import { getConfig } from '../../../lib/config';
	import byteFormatter from '../../../lib/helper/byte-formatter';
	import { uploadTheme } from './themeActions';
	import { setThemeFiles } from './themeStore';

	let inputEl: HTMLInputElement | undefined = $state();

	function handleClick() {
		inputEl?.click();
	}

	function handleUpload() {
		const file = inputEl?.files?.[0];

		if (!file) {
			return toast.error('Please select a file');
		}

		const max = getConfig().limits.max_theme_zip_size;
		if (file.size > max) {
			return toast.error('Max file size is' + byteFormatter(max));
		}

		const toastId = toast.loading('Uploading theme...');

		uploadTheme(file)
			.then(({ files }) => {
				toast.success('Theme uploading completed', { id: toastId });
				setThemeFiles(files);
			})
			.catch((err) => {
				toast.error(err.message, { id: toastId });
			});
	}
</script>

<Tooltip text="Upload a theme from a zip file">
	<Button color="input" size="small" style="font-size:13px" on:click={handleClick}>
		{#snippet start()}
			<IconCloudUpload size={16} />
		{/snippet}
		Upload
	</Button>

	<input
		bind:this={inputEl}
		type="file"
		accept="zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed"
		onchange={handleUpload}
		style="display:none"
	/>
</Tooltip>
