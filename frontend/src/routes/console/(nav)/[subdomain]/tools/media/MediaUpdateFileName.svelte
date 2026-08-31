<script lang="ts">
	import { Modal, TextInput, FormControl, Validation, toast } from '@hyvor/design/components';
	import { updateMedia } from './mediaActions';
	import { createEventDispatcher } from 'svelte';
	import type { Media } from '../../../../lib/types';
	import { toKebabCase } from './mediaUtils';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	let loading = $state(false);

	const dispatch = createEventDispatcher();

	async function handleChangeName() {
		loading = true;
		updateMedia(media.id, { name })
			.then((res) => {
				toast.success(i18n.t('console.theme.fileNameUpdated'));
				dispatch('update', res);
				show = false;
			})
			.catch((err) => toast.error(err.message))
			.finally(() => {
				loading = false;
			});
	}

	interface Props {
		show: boolean;
		media: Media;
	}

	let { show = $bindable(), media }: Props = $props();

	let name = $state(media.name);

	let startExt = $derived(getExtension(name));

	function getExtension(n: string) {
		const parts = n.split('.');
		return (parts[parts.length - 1] || '').trim();
	}
	let ext = $derived(getExtension(name));
	let kebabName = $derived(toKebabCase(name));
</script>

<div>
	<Modal
		bind:show
		closeOnEscape={false}
		title={i18n.t('console.tools.media.updateFileName')}
		footer={{
			cancel: {
				text: i18n.t('console.common.cancel')
			},
			confirm: {
				text: i18n.t('console.common.update')
			}
		}}
		on:cancel={() => (show = false)}
		on:confirm={handleChangeName}
		{loading}
	>
		<FormControl>
			<TextInput
				bind:value={name}
				autofocus
				label={i18n.t('console.theme.fileName')}
				on:keyup={(e) => {
					if (e.key === 'Enter') {
						handleChangeName();
					}
				}}
			/>
			{#if ext !== startExt}
				<Validation state="warning">
					<T
						key="console.tools.media.extensionChangeWarning"
						params={{ strong: { element: 'strong' }, from: startExt, to: ext }}
					/>
				</Validation>
			{/if}
			{#if name !== kebabName}
				<Validation state="warning">
					<T
						key="console.tools.media.kebabNameWarning"
						params={{ strong: { element: 'strong' }, name: kebabName }}
					/>
				</Validation>
			{/if}
		</FormControl>
	</Modal>
</div>
