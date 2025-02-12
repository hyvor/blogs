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
			fromError = 'From is required.';
			return;
		}

		if (!from.startsWith('/')) {
			fromError = 'From value should be a relative path starting with /';
			return;
		}

		if (!to) {
			toError = 'To is required.';
			return;
		}

		if (!isValidUrl(to)) {
			toError = 'To value should be a valid URL.';
			return;
		}

		if (isCreating) {
			loading = true;
			createRedirect(dynamic, from, to, type)
				.then((res) => {
					toast.success('Redirect created successfully');
					dispatch('create', res);
					show = false;
				})
				.catch((err) => {
					if (err.message === 'invalid_path_regex') {
						fromError = 'Invalid regex expression';
						return;
					}
					if (err.message === 'path_already_exists') {
						fromError = 'A redirect already exists for this path.';
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
					toast.success('Redirect updated.');
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

<Modal title={isCreating ? 'Add new redirect' : 'Edit redirect'} {loading} bind:show>
	<SplitControl label="Dynamic" caption="Match a path dynamically using a pattern.">
		<div style="display: flex; align-items: center;">
			<FormControl>
				<Switch bind:checked={dynamic} disabled={isCreating ? $dynamicRedirectsStore >= 5 : true} />
			</FormControl>
			<Text small light style="margin-left:15px; margin-bottom: 2%"
				>{Math.max(5 - $dynamicRedirectsStore, 0)}/5 remaining
			</Text>
		</div>

		<Link href="/docs/redirects#dynamic" color="accent" style="font-size:small" target="_blank"
			>Refer docs for more details.{#snippet end()}
				<IconBoxArrowUpRight />
			{/snippet}</Link
		>
	</SplitControl>
	<SplitControl label="From" caption="Which path to match">
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

	<SplitControl label="To" caption="An absolute URL to redirect to">
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

	<SplitControl label="Type" caption="Permanent redirects are cached by browsers.">
		<InputGroup>
			<Radio name="type" value="permanent" bind:group={type}>Permanent (301)</Radio>
			<Radio name="type" value="temporary" bind:group={type}>Temporary (302)</Radio>
		</InputGroup>
	</SplitControl>

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={() => (show = false)}>Cancel</Button>

			<Button on:click={handleClick} disabled={isButtonDisabled}>
				{isCreating ? 'Add' : 'Save'}
			</Button>
		</ButtonGroup>
	{/snippet}
</Modal>
