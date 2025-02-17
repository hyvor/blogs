<script lang="ts">
	import { IconButton, TextInput, Tooltip, confirm } from '@hyvor/design/components';
	import type { EditorView } from 'prosemirror-view';
	import { NodeSelection } from 'prosemirror-state';
	import schema from '../../schema';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconTrash from '@hyvor/icons/IconTrash';
	import { onMount } from 'svelte';

	interface Props {
		src: string | null;
		alt: string | null;
		width: number | null;
		height: number | null;
		getPos: () => number | undefined;
		view: EditorView;
	}

	let { src, alt, width, height, getPos, view }: Props = $props();

	let imgEl: HTMLImageElement | undefined = $state();

	let displayWidth: string = $state('0');
	let displayHeight: string = $state('0');

	function setDisplaySize() {
		if (!imgEl) return;
		displayWidth = Math.floor(width ? width : imgEl?.naturalWidth).toString();
		displayHeight = Math.floor(height ? height : imgEl?.naturalHeight).toString();
	}

	function handleLoad() {
		setDisplaySize();
	}

	function updateProps(
		props: Partial<{
			src?: string | null;
			alt?: string | null;
			width?: number | null;
			height?: number | null;
		}>
	) {
		const pos = getPos();
		if (pos === undefined) return;

		view.dispatch(
			view.state.tr.setNodeMarkup(pos, undefined, {
				...{
					src,
					alt,
					width,
					height
				},
				...props
			})
		);
	}

	function handleAltInput(e: any) {
		const pos = getPos();
		if (pos === undefined) return;
		updateProps({
			alt: e.target.value
		});
	}

	function handleRangeInput(e: any) {
		const value = parseInt(e.target.value);
		let width: number | null, height: number | null;
		if (!imgEl) return;
		if (value === 100) {
			width = null;
			height = null;
		} else {
			width = (imgEl.naturalWidth * value) / 100;
			height = (imgEl.naturalHeight * value) / 100;
		}

		updateProps({
			width,
			height
		});
	}

	function handleChangeClick() {
		// TODO: abstract this away
		return;

		/* const div = document.createElement("div");
    document.body.appendChild(div);

    const selector = mount(FileUploader, {
      target: div,
      props: {
        type: "image",
        onselect: (file) => {
          destroy();
          changeImage(file);
        },
        onclose: () => {
          destroy();
        },
      },
    }); 

    function destroy() {
      unmount(selector);
      div.remove();
    }*/
	}

	async function handleDelete() {
		if (
			await confirm({
				title: 'Remove image',
				content:
					'Are you sure you want to remove this image? It will not be deleted from the media library.',
				confirmText: 'Yes, remove it',
				danger: true
			})
		) {
			const pos = getPos();

			if (pos === undefined) return;

			// figure
			const figureSel = NodeSelection.create(view.state.doc, pos - 1);

			view.dispatch(view.state.tr.delete(figureSel.from, figureSel.to));

			view.focus();
		}
	}

	function changeImage(image: any) {
		const pos = getPos();

		if (pos === undefined) return;

		updateProps({
			src: image.url as string,
			alt: image.unsplash?.alt || ''
		});

		if (image.from === 'unsplash' && image.unsplash) {
			const nodeSel = NodeSelection.create(view.state.doc, pos + 1);

			const utm = '?utm_source=hyvor_blogs&utm_medium=referral';

			const tr = view.state.tr;
			const newNode = schema.nodes.figcaption!.create({}, [
				schema.text('Photo by '),
				schema.text(image.unsplash.author, [
					schema.marks.link!.create({
						href: image.unsplash.author_url + utm
					})
				]),
				schema.text(' on '),
				schema.text('Unsplash', [
					schema.marks.link!.create({
						href: 'https://unsplash.com/' + utm
					})
				])
			]);

			tr.replaceWith(nodeSel.from, nodeSel.to, newNode);

			view.dispatch(tr);
		}
	}

	onMount(() => {
		setDisplaySize();
	});
	$effect(() => {
		width;
		height;
		setDisplaySize();
	});
</script>

<div class="image-node-wrap">
	<div class="top">
		<div class="left">
			<TextInput size="small" placeholder="Add alt text..." on:input={handleAltInput} value={alt}>
				{#snippet start()}
					<span>ALT</span>
				{/snippet}
			</TextInput>
		</div>

		<div class="right">
			{#if imgEl}
				<div class="range-wrap">
					<span class="size">
						Size ({displayWidth} x {displayHeight})
					</span>
					<input
						type="range"
						min={1}
						max={100}
						step={1}
						oninput={handleRangeInput}
						value={width ? (width / imgEl.naturalWidth) * 100 : 100}
					/>
				</div>
			{/if}

			<div>
				<Tooltip text="Change image">
					<IconButton size="small" color="input" on:click={handleChangeClick}>
						<IconPencil size={12} />
					</IconButton>
				</Tooltip>

				<Tooltip text="Remove image">
					<IconButton size="small" color="input" on:click={handleDelete}>
						<IconTrash size={12} />
					</IconButton>
				</Tooltip>
			</div>
		</div>
	</div>
	<div class="img-wrap">
		<img
			{src}
			{alt}
			bind:this={imgEl}
			width={width ? width : undefined}
			height={height ? height : undefined}
			onload={handleLoad}
		/>
	</div>
</div>

<style>
	.image-node-wrap {
		background-color: #fafafa;
		border-radius: 20px;
		display: flex;
		flex-direction: column;
	}

	.top {
		border-bottom: 1px solid #eee;
		padding: 15px;
		display: flex;
		align-items: center;
		gap: 5px;
	}

	.left {
		flex: 1;
	}

	.right {
		display: flex;
		align-items: center;
		gap: 6px;
	}

	.img-wrap {
		padding: 15px;
	}

	.range-wrap {
		display: inline-flex;
		align-items: center;
		position: relative;
	}

	.size {
		font-size: 10px;
		color: var(--text-light);
		margin-right: 5px;
	}
</style>
