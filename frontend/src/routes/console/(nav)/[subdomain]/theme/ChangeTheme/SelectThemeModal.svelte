<script lang="ts">
	import {
		Loader,
		Modal,
		Button,
		ActionList,
		ActionListItem,
		Text,
		confirm,
		Callout,
		toast
	} from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { changeTheme, loadThemes } from '../themeActions';
	import type { Theme } from '../../../../lib/types';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconExclamation from '@hyvor/icons/IconExclamation';
	import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';

	import { setThemeFiles } from '../themeStore';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		show?: boolean;
	}

	let { show = $bindable(false) }: Props = $props();

	let isLoading = $state(true);
	let themes: Theme[] = $state([]);

	onMount(() => {
		loadThemes()
			.then((res) => {
				themes = res;
			})
			.finally(() => (isLoading = false));
	});

	async function handleClick(name: string) {
		if (
			await confirm({
				title: i18n.t('console.theme.changeConfirm.title'),
				content: i18n.t('console.theme.changeConfirm.content'),
				confirmText: i18n.t('console.theme.changeConfirm.confirm')
			})
		) {
			show = false;

			const toastId = toast.loading(i18n.t('console.theme.changing'));

			changeTheme(name)
				.then((files) => {
					toast.success(i18n.t('console.theme.changed'), { id: toastId });
					setThemeFiles(files);
				})
				.catch((e) => toast.error(e.message, { id: toastId }));
		}
	}
</script>

<Modal
	bind:show
	footer={{
		cancel: {
			text: i18n.t('console.common.close')
		},
		confirm: false
	}}
	closeOnOutsideClick={false}
	size="small"
>
	{#snippet title()}
		<div class="title">
			{i18n.t('console.theme.chooseTheme')}
			<Button as="a" href="/themes" target="_blank" size="small">
				{i18n.t('console.theme.previewThemes')}
				{#snippet end()}
					<IconBoxArrowUpRight size={12} />
				{/snippet}
			</Button>
		</div>
	{/snippet}

	{#if isLoading}
		<Loader block padding={150} />
	{:else}
		<Callout type="warning" style="text-align:initial;margin-bottom:20px;font-size:14px">
			{#snippet icon()}
				<IconExclamationCircle size={16} />
			{/snippet}
			{i18n.t('console.theme.changeWarning')}
		</Callout>

		<ActionList>
			{#each themes as theme (theme.id)}
				<ActionListItem on:click={() => handleClick(theme.name)}>
					{theme.name}

					{#snippet end()}
						<Text light>
							v{theme.latest_version}
						</Text>
					{/snippet}
				</ActionListItem>
			{/each}
		</ActionList>
	{/if}
</Modal>

<style>
	.title {
		font-weight: 600;
		text-align: left;
		font-size: 18px;
	}
</style>
