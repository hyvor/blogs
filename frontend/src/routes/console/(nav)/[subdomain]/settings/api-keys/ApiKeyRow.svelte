<script lang="ts">
	import { Button, IconButton, TableRow, Tooltip, confirm, toast } from '@hyvor/design/components';
	import type { ApiKey } from '../../../../lib/types';
	import IconArrowCounterclockwise from '@hyvor/icons/IconArrowCounterclockwise';
	import IconCopy from '@hyvor/icons/IconCopy';
	import IconTrash from '@hyvor/icons/IconTrash';

	import { deleteApiKey, regenerateApiKey } from './apiKeysActions';
	import { createEventDispatcher } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	const TYPE_KEYS = {
		console: 'console.settings.apiKeys.types.console',
		delivery: 'console.settings.apiKeys.types.delivery'
	} as const;

	interface Props {
		apiKey: ApiKey;
	}

	let { apiKey }: Props = $props();

	const dispatch = createEventDispatcher();

	function handleCopy() {
		navigator.clipboard.writeText(apiKey.api_key);
		toast.success(i18n.t('console.common.copied'));
	}

	async function handleRegenerate() {
		if (
			await confirm({
				title: i18n.t('console.settings.apiKeys.regenerateTitle'),
				content: i18n.t('console.settings.apiKeys.regenerateContent'),
				confirmText: i18n.t('console.settings.apiKeys.regenerateConfirm'),
				danger: true
			})
		) {
			const toastId = toast.loading(i18n.t('console.settings.apiKeys.regenerating'));

			regenerateApiKey(apiKey.id)
				.then((newApiKey) => {
					dispatch('update', newApiKey);
					toast.success(i18n.t('console.settings.apiKeys.regenerated'), { id: toastId });
				})
				.catch((err) => {
					toast.error(err.message, { id: toastId });
				});
		}
	}

	async function handleDelete() {
		if (
			await confirm({
				title: i18n.t('console.settings.apiKeys.deleteTitle'),
				content: i18n.t('console.settings.apiKeys.deleteContent'),
				confirmText: i18n.t('console.common.yesDelete'),
				danger: true
			})
		) {
			const toastId = toast.loading(i18n.t('console.tools.media.deleting'));

			deleteApiKey(apiKey.id)
				.then(() => {
					toast.success(i18n.t('console.tools.media.deleted'), { id: toastId });
					dispatch('delete', apiKey.id);
				})
				.catch((err) => {
					toast.error(err.message, { id: toastId });
				});
		}
	}
</script>

<TableRow>
	<div>{apiKey.name}</div>
	<div class="api-type">{i18n.t(TYPE_KEYS[apiKey.type])}</div>
	<div>
		<Tooltip text={i18n.t('console.settings.apiKeys.copyKey')}>
			<IconButton color="gray" variant="fill-light" on:click={handleCopy}>
				<IconCopy size={12} />
			</IconButton>
		</Tooltip>

		<Tooltip text={i18n.t('console.settings.apiKeys.regenerateTitle')}>
			<IconButton color="gray" variant="fill-light" on:click={handleRegenerate}>
				<IconArrowCounterclockwise size={12} />
			</IconButton>
		</Tooltip>

		<Tooltip text={i18n.t('console.settings.apiKeys.deleteTitle')}>
			<IconButton color="red" variant="fill-light" on:click={handleDelete}>
				<IconTrash size={12} />
			</IconButton>
		</Tooltip>
	</div>

	<div></div>

	<!-- <div>
        <button
            class="icon-button"
            onClick={() => setIsDeleting(true)}><Trash size={10} /></button>
    </div> -->

	<!-- {
        isDeleting ?
            <PopupConfirm
                title={i18n.t('console.settings.apiKeys.deleteTitle')}
                text="Please confirm to delete this API Key"
                name="Delete"
                buttonClass="danger"
                onClick={handleDelete}
                onCancel={() => setIsDeleting(false)}
            />
            : null
    } -->
</TableRow>
