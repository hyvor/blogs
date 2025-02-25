<script lang="ts">
	import { Button, TextInput, Validation } from '@hyvor/design/components';
	import IconArrowReturnLeft from '@hyvor/icons/IconArrowReturnLeft';
	import { createEventDispatcher, onMount } from 'svelte';
	import { getHeadingsFromContent, type Heading } from '../../../helpers';
	import { editorContent } from '../../../store';

	interface Props {
		input?: string;
	}

	let { input = $bindable('') }: Props = $props();

	let isRelative = $derived(!/^[a-zA-Z0-9]+:\/\//.test(input));
	let isAnchor = $derived(/^#/.test(input));
	let headings: Heading[] = $state([]);
	let isAnchorAvailable = $derived(
		headings.find((heading) => heading.id === input.replace('#', ''))
	);

	const dispatch = createEventDispatcher();

	function handleClick() {
		dispatch('add', input.trim());
	}

	function handleKeyup(e: KeyboardEvent) {
		if (e.key === 'Enter') {
			handleClick();
		}
	}

	let inputEl: HTMLInputElement | undefined = $state();

	onMount(() => {
		inputEl?.focus();
	});

	editorContent.subscribe((content) => {
		headings = getHeadingsFromContent(content);
	});
</script>

<TextInput
	placeholder="Paste a link..."
	block
	bind:value={input}
	bind:input={inputEl}
	on:keyup={handleKeyup}
/>

{#if input.trim() !== '' && (isRelative || isAnchor)}
	<div class="warning-wrap">
		<Validation state={isAnchor ? (isAnchorAvailable ? 'success' : 'error') : 'warning'}>
			{#if isAnchor && !isAnchorAvailable}
				You are adding a <strong>missing anchor link</strong>.
			{:else if isAnchor && isAnchorAvailable}
				You are adding an <strong>anchor link</strong>.
			{:else if isRelative}
				You are adding a <strong>relative link</strong>.
			{/if}
		</Validation>
	</div>
{/if}

<div class="buttons">
	{#if input.trim().length !== 0}
		<span class="enter"> Press Enter </span>
	{/if}

	<Button variant="fill" disabled={input.trim().length === 0} on:click={handleClick}>
		Add Link
		{#snippet end()}
			<IconArrowReturnLeft size={12} />
		{/snippet}
	</Button>
</div>

<style lang="scss">
	.buttons {
		margin-top: 15px;
		text-align: right;
		margin-bottom: 15px;
	}

	.warning-wrap {
		margin-top: 10px;
	}

	.enter {
		font-size: 12px;
		margin-right: 5px;
		color: var(--text-light);
	}
</style>
