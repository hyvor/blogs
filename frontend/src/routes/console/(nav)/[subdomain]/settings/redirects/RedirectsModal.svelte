<script lang="ts">
	import {
		Button,
		ButtonGroup,
		FormControl,
		InputGroup,
		Link,
		Modal,
		Radio,
		SplitControl,
		Switch,
		Text,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import type { Redirect } from '../../../../lib/types';
	import { createRedirect, updateRedirect } from './redirectActions';
	import { createEventDispatcher } from 'svelte';
	import { isValidUrl } from '../../../../lib/helper/is-valid-url';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import { dynamicRedirectsStore } from './dynamicRedirect';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		redirect?: Redirect | null;
		show?: boolean;
	}

	let { redirect = null, show = $bindable(false) }: Props = $props();
	let loading = $state(false);
	const isCreating = redirect === null;

	const dispatch = createEventDispatcher();

	let dynamic = $state(redirect ? redirect.dynamic : false);
	let from = $state(redirect ? redirect.path : '');
	let to = $state(redirect ? redirect.to : '');
	let type: 'permanent' | 'temporary' = $state(redirect ? redirect.type : 'permanent');

	let fromError: null | string = $state(null);
	let toError: null | string = $state(null);

	function handleClick() {
		fromError = null;
		toError = null;

		if (!from) {
			fromError = i18n.t('console.settings.redirects.validation.fromRequired');
			return;
		}

		if (!from.startsWith('/')) {
			fromError = i18n.t('console.settings.redirects.validation.fromRelative');
			return;
		}

		if (!to) {
			toError = i18n.t('console.settings.redirects.validation.toRequired');
			return;
		}

		if (!isValidUrl(to)) {
			toError = i18n.t('console.settings.redirects.validation.toUrl');
			return;
		}

		if (isCreating) {
			loading = true;
			createRedirect(dynamic, from, to, type)
				.then((res) => {
					toast.success(i18n.t('console.settings.redirects.created'));
					dispatch('create', res);
					show = false;
				})
				.catch((err) => {
					if (err.message === 'invalid_path_regex') {
						fromError = i18n.t('console.settings.redirects.validation.invalidRegex');
						return;
					}
					if (err.message === 'path_already_exists') {
						fromError = i18n.t('console.settings.redirects.validation.pathExists');
						return;
					}
					toast.error(err.message);
				})
				.finally(() => {
					loading = false;
				});
		} else {
			loading = true;
			updateRedirect(redirect!.id, dynamic, from, to, type)
				.then((res) => {
					toast.success(i18n.t('console.settings.redirects.updated'));
					dispatch('update', res);
					show = false;
				})
				.catch((err) => {
					toast.error(err.message);
				})
				.finally(() => {
					loading = false;
				});
		}
	}

	let isButtonDisabled = $derived(
		!(
			isCreating ||
			dynamic !== redirect!.dynamic ||
			from !== redirect!.path ||
			to !== redirect!.to ||
			type !== redirect!.type
		)
	);
</script>

<Modal
	title={isCreating
		? i18n.t('console.settings.redirects.addTitle')
		: i18n.t('console.settings.redirects.editRedirect')}
	{loading}
	bind:show
>
	<SplitControl
		label={i18n.t('console.settings.redirects.dynamic')}
		caption={i18n.t('console.settings.redirects.dynamicCaption')}
	>
		<div style="display: flex; align-items: center;">
			<FormControl>
				<Switch bind:checked={dynamic} disabled={isCreating ? $dynamicRedirectsStore >= 5 : true} />
			</FormControl>
			<Text small light style="margin-left:15px; margin-bottom: 2%">
				{i18n.t('console.settings.redirects.dynamicRemaining', {
					count: Math.max(5 - $dynamicRedirectsStore, 0)
				})}
			</Text>
		</div>

		<Link href="/docs/redirects#dynamic" color="accent" style="font-size:small" target="_blank">
			{i18n.t('console.settings.redirects.referDocs')}
			{#snippet end()}
				<IconBoxArrowUpRight />
			{/snippet}</Link
		>
	</SplitControl>
	<SplitControl
		label={i18n.t('console.settings.redirects.from')}
		caption={i18n.t('console.settings.redirects.fromCaption')}
	>
		<FormControl>
			<TextInput
				bind:value={from}
				placeholder={dynamic ? '/welcome(.*)' : '/welcome'}
				block
				state={fromError ? 'error' : undefined}
				autofocus
			/>

			{#if fromError}
				<Validation state="error">
					{fromError}
				</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl
		label={i18n.t('console.settings.redirects.to')}
		caption={i18n.t('console.settings.redirects.toCaption')}
	>
		<FormControl>
			<TextInput
				bind:value={to}
				placeholder={dynamic ? 'https://hyvor.com/$1' : 'https://hyvor.com'}
				block
				state={toError ? 'error' : undefined}
			/>

			{#if toError}
				<Validation state="error">
					{toError}
				</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl
		label={i18n.t('console.settings.redirects.type')}
		caption={i18n.t('console.settings.redirects.typeCaption')}
	>
		<InputGroup>
			<Radio name="type" value="permanent" bind:group={type}>
				{i18n.t('console.settings.redirects.permanent')}
			</Radio>
			<Radio name="type" value="temporary" bind:group={type}>
				{i18n.t('console.settings.redirects.temporary')}
			</Radio>
		</InputGroup>
	</SplitControl>

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (show = false)}>
				{i18n.t('console.common.cancel')}
			</Button>

			<Button on:click={handleClick} disabled={isButtonDisabled}>
				{isCreating ? i18n.t('console.common.add') : i18n.t('console.common.save')}
			</Button>
		</ButtonGroup>
	{/snippet}
</Modal>
