<script lang="ts">
	import {
		FormControl,
		Modal,
		SplitControl,
		Switch,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import type { Route } from '../../../../lib/types';
	import { createRoute, updateRoute } from './routeActions';
	import { createEventDispatcher } from 'svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		show?: boolean;
		route?: null | Route;
	}

	let { show = $bindable(false), route = null }: Props = $props();

	let name = $state(route?.name || '');
	let match = $state(route?.match || '');
	let template = $state(route?.template || '');
	let postsFilter = $state(route?.posts_filter || '');
	let contentType = $state(route?.content_type || '');
	let postsFilterEnabled = $state(route?.posts_filter !== null);

	let nameError: null | string = $state(null);
	let matchError: null | string = $state(null);
	let templateError: null | string = $state(null);
	let postsFilterError: null | string = null;
	let contentTypeError: null | string = null;

	let isCreating = $state(false);

	function validate() {
		let isValid = true;

		if (name.trim().length === 0) {
			nameError = i18n.t('console.common.nameRequired');
			isValid = false;
		}

		if (match.trim().length === 0) {
			matchError = i18n.t('console.settings.routes.validation.matchRequired');
			isValid = false;
		}

		if (match[0] !== '/') {
			matchError = i18n.t('console.settings.routes.validation.matchSlash');
			isValid = false;
		}

		if (!template) {
			templateError = i18n.t('console.settings.routes.validation.templateRequired');
			isValid = false;
		}

		return isValid;
	}

	const dispatch = createEventDispatcher();

	function handleCreate() {
		if (!validate()) return;

		isCreating = true;

		createRoute({
			name,
			match,
			template,
			posts_filter: postsFilterEnabled ? postsFilter : null,
			content_type: contentType
		})
			.then((res) => {
				toast.success(i18n.t('console.settings.routes.created'));
				dispatch('create', res);
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				show = false;
			});
	}

	function handleUpdate() {
		if (!validate()) return;

		isCreating = true;

		updateRoute(route!.id, {
			name,
			match,
			template,
			posts_filter: postsFilterEnabled ? postsFilter : null,
			content_type: contentType
		})
			.then((res) => {
				toast.success(i18n.t('console.settings.routes.updated'));
				dispatch('update', res);
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				show = false;
			});
	}

	const flex = [2, 3];
</script>

<Modal
	title={route ? 'Update Route' : 'Create Route'}
	bind:show
	footer={{
		confirm: {
			text: route ? 'Update' : 'Create'
		}
	}}
	on:confirm={() => {
		if (route) {
			handleUpdate();
		} else {
			handleCreate();
		}
	}}
	loading={isCreating}
>
	<SplitControl
		{flex}
		label={i18n.t('console.common.name')}
		caption={i18n.t('console.common.justForReference')}
	>
		<FormControl>
			<TextInput
				block
				placeholder={i18n.t('console.settings.routes.namePlaceholder')}
				bind:value={name}
				state={nameError ? 'error' : undefined}
				autofocus
			/>
			{#if nameError}
				<Validation state="error">{nameError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl
		{flex}
		label={i18n.t('console.settings.routes.match')}
		caption={i18n.t('console.settings.routes.matchCaption')}
	>
		<FormControl>
			<TextInput
				block
				placeholder="/path"
				bind:value={match}
				state={matchError ? 'error' : undefined}
			/>
			{#if matchError}
				<Validation state="error">{matchError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl
		{flex}
		label={i18n.t('console.settings.routes.template')}
		caption={i18n.t('console.settings.routes.templateCaption')}
	>
		<FormControl>
			<TextInput
				block
				placeholder="new,index"
				bind:value={template}
				state={templateError ? 'error' : undefined}
			/>
			{#if templateError}
				<Validation state="error">{templateError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl
		{flex}
		label={i18n.t('console.settings.routes.postsFilter')}
		caption={i18n.t('console.settings.routes.postsFilterCaption')}
	>
		<FormControl>
			<Switch
				bind:checked={postsFilterEnabled}
				label={i18n.t('console.settings.routes.enablePostsFilter')}
			/>
			{#if postsFilterEnabled}
				<TextInput
					block
					placeholder={i18n.t('console.settings.routes.postsFilterPlaceholder')}
					bind:value={postsFilter}
					state={postsFilterError ? 'error' : undefined}
				/>
			{/if}
			{#if postsFilterError}
				<Validation state="error">{postsFilterError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	<SplitControl
		{flex}
		label={i18n.t('console.settings.routes.contentType')}
		caption={i18n.t('console.settings.routes.contentTypeCaption')}
	>
		<FormControl>
			<TextInput
				block
				placeholder="text/html"
				bind:value={contentType}
				state={contentTypeError ? 'error' : undefined}
			/>
			{#if contentTypeError}
				<Validation state="error">{contentTypeError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>
</Modal>
