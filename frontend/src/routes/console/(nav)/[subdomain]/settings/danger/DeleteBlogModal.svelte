<script lang="ts">
	import {
		Button,
		ButtonGroup,
		FormControl,
		Modal,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import { blogStore } from '../../../../lib/stores/blogStore';
	import { deleteBlogDangerous } from './dangerActions';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	interface Props {
		show?: boolean;
	}

	let { show = $bindable(false) }: Props = $props();

	let subdomain = $state('');
	let error: null | string = $state(null);

	function handleDelete() {
		if (subdomain !== $blogStore.subdomain) {
			error = i18n.t('console.settings.danger.delete.subdomainMismatch');
			return;
		}

		show = false;

		const toastId = toast.loading(i18n.t('console.settings.danger.delete.pleaseWait'));

		deleteBlogDangerous()
			.then(() => {
				toast.success(i18n.t('console.settings.danger.delete.inProgress'), {
					id: toastId
				});
				setTimeout(() => {
					window.location.href = '/';
				}, 3000);
			})
			.catch(() => {
				toast.error(i18n.t('console.settings.danger.delete.failed'), { id: toastId });
			});
	}
</script>

<Modal title={i18n.t('console.settings.danger.deleteBlog')} bind:show>
	<T
		key="console.settings.danger.delete.intro"
		params={{
			danger: { element: 'strong', props: { style: 'color:var(--red-dark)' } },
			strong: { element: 'strong' },
			subdomain: $blogStore.subdomain
		}}
	/>

	<div style="margin-top:15px;">
		<FormControl>
			<TextInput
				bind:value={subdomain}
				block
				placeholder={i18n.t('console.settings.danger.delete.placeholder', {
					subdomain: $blogStore.subdomain
				})}
				state={error
					? 'error'
					: subdomain === ''
						? 'default'
						: subdomain === $blogStore.subdomain
							? 'success'
							: 'error'}
			/>

			{#if error}
				<Validation state="error">{error}</Validation>
			{/if}
		</FormControl>
	</div>

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (show = false)}>
				{i18n.t('console.common.cancel')}
			</Button>

			<Button on:click={handleDelete} color="red">
				{i18n.t('console.settings.danger.delete.confirm')}
			</Button>
		</ButtonGroup>
	{/snippet}
</Modal>
