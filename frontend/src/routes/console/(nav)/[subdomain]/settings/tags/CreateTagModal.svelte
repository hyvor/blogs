<script lang="ts">
	import {
		Button,
		ButtonGroup,
		Caption,
		FormControl,
		Link,
		Modal,
		SplitControl,
		Switch,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import { createTag } from './tagActions';
	import { createEventDispatcher } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	let name: string = $state('');
	let isPrivate = $state(false);

	const dispatch = createEventDispatcher();

	function handleClick() {
		const toastId = toast.loading(i18n.t('console.settings.tags.creating'));

		show = false;

		createTag(name, isPrivate)
			.then((res) => {
				toast.success(i18n.t('console.settings.tags.created'), { id: toastId });
				dispatch('create', res);
			})
			.catch((err) => {
				toast.error(err.message, { id: toastId });
			});
	}

	let isButtonDisabled = $derived(name.trim().length === 0);
</script>

<Modal title={i18n.t('console.settings.tags.create')} bind:show>
	<SplitControl
		label={i18n.t('console.common.name')}
		caption={i18n.t('console.settings.tags.nameCaption')}
	>
		<FormControl>
			<TextInput
				bind:value={name}
				placeholder={i18n.t('console.settings.tags.namePlaceholder')}
				block
				autofocus
			/>
		</FormControl>
	</SplitControl>

	<SplitControl label={i18n.t('console.settings.tags.private')}>
		{#snippet caption()}
			<Caption>
				<T
					key="console.settings.tags.privateCaption"
					params={{
						link: {
							element: 'a',
							props: { href: '/docs/tags#private', target: '_blank', class: 'hds-link' }
						}
					}}
				/>
			</Caption>
		{/snippet}
		<Switch bind:checked={isPrivate} />
	</SplitControl>

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (show = false)}
				>{i18n.t('console.common.cancel')}</Button
			>

			<Button on:click={handleClick} disabled={isButtonDisabled}
				>{i18n.t('console.common.create')}</Button
			>
		</ButtonGroup>
	{/snippet}
</Modal>
