<script lang="ts">
	import { Button, IconButton, Loader, TextInput, Tooltip, confirm, toast } from "@hyvor/design/components";
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

    let loading = false;

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

	function handleFiles(files: FileList | null) {
        loading = true;
        if (!files || files.length === 0) {
            toast.error('No file selected')
            return
        } else if (files.length > 1) {
            toast.error('Select only one audio');
            return;
        }

        const file = files[0];
        loading = true;
        handleFileUpload(file!);
    }

    async function handleFileUpload(file: File) {

        if (file.size > 50 * 1000 * 1000) {
            toast.error("Max size is 50MB");
            deleteNodeAudio();
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
            deleteNodeAudio();
            return;
        }

        var formData = new FormData();
        formData.append('file', file, file.name);
        try {

            const media = await uploadMedia(file);
            console.log(media);
            if (media)
                loading = false;
            // Replace the node with the new one
            const { tr } = view.state;
            const pos = getPos();

            if (pos === undefined) {
                return;
            }
            else {
                tr.replaceWith(pos, pos + 1, schema.nodes.audio!.create({ src: media.url }));
            }
            view.dispatch(tr);

        } catch (e) {
            toast.error('Error uploading file');

            // Delete the node if the upload fails
            deleteNodeAudio();
        }
    }

    async function handleDelete() {
        if (await confirm({
            title: 'Remove audio',
            content: 'Are you sure you want to remove this audio?',
            confirmText: 'Yes, remove it',
            danger: true,
        })) {
            deleteNodeAudio();
        }
    }

    function deleteNodeAudio() {
        const { tr } = view.state;
        const pos = getPos();
        if (pos === undefined)
            return;
        tr.delete(pos, pos + 1);
        view.dispatch(tr);
    }

    function handleChangeClick() {
        fileInputEl.click();
    }

	onMount(() => {
		if (!src) {
			fileInputEl.click(); // Trigger the file input if no audio is selected initially
		}
	});

</script>

<div class="audio-wrap">
    <div class="audio-actions">
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
    <div>
        {#if loading}
            <p><Loader /></p>
        {:else}
            {#if src}
                <audio
                    src={src}
                    bind:this={audioEl}
                    controls
                />
            {:else}
                <p>No audio selected.</p>
            {/if}
        {/if}
        <input
            type="file"
            accept="audio/*"
            bind:this={fileInputEl}
            on:change={() => handleFiles(fileInputEl.files)}
            class="audio-input"
        />
    </div>
</div>

<style>
    .audio-wrap {
    display: flex;
    flex-direction: column;
    }
    .audio-input {
        display: none;
    }
    .audio-actions {
        position: relative;
        align-self: flex-end;
        margin-bottom: 5px;
    }
    audio {
        width: 100%;
    }

</style>