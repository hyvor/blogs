<script lang="ts">
	import { Button, IconButton, TextInput, Tooltip, confirm, toast } from "@hyvor/design/components";
	import type { EditorView } from "prosemirror-view";
	import { NodeSelection } from "prosemirror-state";
	import schema from "../../../../../../../lib/prosemirror/schema";
	import { IconPencil, IconTrash } from "@hyvor/icons";
	import { onMount } from "svelte";
	import { getConfig } from "../../../../../../../lib/config";
	import byteFormatter from "../../../../../../../lib/helper/byte-formatter";
	import { uploadMedia } from "../../../../../../tools/media/mediaActions";

    export let src: string | null;
    export let getPos: () => number | undefined;
    export let view: EditorView;

    let audioEl: HTMLAudioElement;
	let fileInputEl: HTMLInputElement;

    function updateProps(props: Partial<{
        src?: string | null,
    }>) {
        const pos = getPos();
        if (pos === undefined) return;

        view.dispatch(
            view.state.tr.setNodeMarkup(
                pos,
                undefined,
                {
                    ...{
                        src,
                    },
                    ...props,
                }
            )
        )
    }

    function handleChangeClick() {
        console.log('change audio');
		fileInputEl.click(); // Trigger the file input click to open the file browser
    }

	function handleFiles(files: FileList | null) {
        if (!files || files.length === 0) {
            toast.error('No file selected')
            return
        } else if (files.length > 1) {
            toast.error('Select only one audio');
            return;
        }

        const file = files[0];
        handleFileUpload(file!);
    }

    async function handleFileUpload(file: File) {

        if (file.size > 50 * 1000 * 1000) {
            toast.error("Max size is 50MB");
            return;
        }

        const validTypes = [
            'audio/mpeg',
            'audio/ogg',
            'audio/wav',
            'audio/webm'
        ];
        if (!validTypes.includes(file.type)) {
            toast.error('Only mp3, ogg, wav and webm files are allowed');
            return;
        }

        var formData = new FormData();
        formData.append('file', file, file.name);
        try {
            
            const media = await uploadMedia(file);
            // Replace the node with the new one
            const { tr } = view.state;
            const pos = getPos();
            if (pos === undefined)
                return;
            tr.replaceWith(pos, pos + 1, schema.nodes.audio!.create({ src: media.url }));
            view.dispatch(tr);

        } catch (e) {
            toast.error('Error uploading file');
        }
    }

    async function handleDelete() {
        if (await confirm({
            title: 'Remove audio',
            content: 'Are you sure you want to remove this audio?',
            confirmText: 'Yes, remove it',
            danger: true,
        })) {
            const { tr } = view.state;
            const pos = getPos();
            if (pos === undefined)
                return;
            tr.delete(pos, pos + 1);
            view.dispatch(tr);
        }
    }
	onMount(() => {
		if (!src) {
			fileInputEl.click(); // Trigger the file input if no audio is selected initially
		}
	});

</script>

<div class="image-node-wrap">
    <div class="top">
        <div class="right">
            <div>
                <Tooltip text="Change audio">
                    <IconButton 
                        size="small" 
                        color="input"
                        on:click={handleChangeClick}
                    >
                        <IconPencil size={12} />
                    </IconButton>
                </Tooltip>

                <Tooltip text="Remove audio">
                    <IconButton 
                        size="small" 
                        color="input"
                        on:click={handleDelete}
                    >
                        <IconTrash size={12} />
                    </IconButton>
                </Tooltip>
            </div>
        </div>
    </div>
    <div class="audio-wrap">
        {#if src}
            <audio
                src={src}
                bind:this={audioEl}
                controls
            />
        {:else}
            <p>No audio selected.</p>
        {/if}
        <input
            type="file"
            accept="audio/*"
            bind:this={fileInputEl}
            on:change={() => handleFiles(fileInputEl.files)}
            style="display: none;"
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
