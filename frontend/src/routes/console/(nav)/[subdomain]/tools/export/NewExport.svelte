<script>
	import { Button, Radio, SplitControl, confirm, toast } from '@hyvor/design/components';
	import { startExport } from './exportActions';
	import { createEventDispatcher } from 'svelte';

	const dispatch = createEventDispatcher();

	function dispatchComplete() {
		dispatch('complete');
	}

	async function exportNow() {
		if (
			await confirm({
				title: 'Export Data',
				content:
					'You are about to export your data. This may take a few minutes. You can track the progress in Export History.',
				confirmText: 'Export Now'
			})
		) {
			const toastId = toast.loading('Exporting...');

			startExport()
				.then(() => {
					toast.success('Export started, you can track the progress in Export History.', {
						id: toastId
					});
					dispatchComplete();
				})
				.catch(() =>
					toast.error('Failed to start export. Please try again later.', { id: toastId })
				);
		}
	}
</script>

<SplitControl label="Export Format">
	<Radio checked={true}>Hyvor Blog JSON</Radio>
</SplitControl>

<div class="button-wrap">
	<Button on:click={exportNow}>Export Now</Button>
</div>

<style>
	.button-wrap {
		padding: 30px;
		text-align: center;
	}
</style>
