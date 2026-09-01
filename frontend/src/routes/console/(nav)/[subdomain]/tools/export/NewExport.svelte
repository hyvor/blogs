<script>
	import { Button, Radio, SplitControl, confirm, toast } from '@hyvor/design/components';
	import { startExport } from './exportActions';
	import { createEventDispatcher } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	const dispatch = createEventDispatcher();

	function dispatchComplete() {
		dispatch('complete');
	}

	async function exportNow() {
		if (
			await confirm({
				title: i18n.t('console.tools.export.confirm.title'),
				content: i18n.t('console.tools.export.confirm.content'),
				confirmText: i18n.t('console.tools.export.exportNow')
			})
		) {
			const toastId = toast.loading(i18n.t('console.tools.export.exporting'));

			startExport()
				.then(() => {
					toast.success(i18n.t('console.tools.export.started'), {
						id: toastId
					});
					dispatchComplete();
				})
				.catch(() => toast.error(i18n.t('console.tools.export.failed'), { id: toastId }));
		}
	}
</script>

<SplitControl label={i18n.t('console.tools.export.format')}>
	<Radio checked={true}>Hyvor Blog JSON</Radio>
</SplitControl>

<div class="button-wrap">
	<Button on:click={exportNow}>{i18n.t('console.tools.export.exportNow')}</Button>
</div>

<style>
	.button-wrap {
		padding: 30px;
		text-align: center;
	}
</style>
