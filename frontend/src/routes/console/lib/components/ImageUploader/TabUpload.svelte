<script lang="ts">
	import { Button, Loader, TextInput } from "@hyvor/design/components";
	import { IconArrowReturnLeft } from "@hyvor/icons";

    export let isUploading = false;

    let inputEl: HTMLInputElement;

    let byUrl = '';

    function getCtrl() {
        const platform = (navigator as any)?.userAgentData?.platform || navigator?.platform || 'unknown'
        return platform.match(/mac/i) ? '⌘' : 'Ctrl';
    }

</script>

<div class="tab">

    <input
        type="file"
        accept="image/*"
        style="display:none"
        bind:this={inputEl}
        on:change={() => {}}
    />

    {#if isUploading}
        <Loader full />
    {:else}

        <div class="upload-wrap">
            <div class="upload-area">
                Drag and drop, paste ({getCtrl()} + v), or click to upload
            </div>
        </div>

        <div class="by-url-wrap">
            <div class="title">
                or, Upload by URL
            </div>

            <div class="input-button">
                <TextInput
                    block 
                    placeholder="Enter image URL"
                    bind:value={byUrl}
                />
                <Button
                    disabled={byUrl.trim() === ''}
                >
                    Fetch <IconArrowReturnLeft slot="end" />
                </Button>
            </div>
        </div>

        <!-- <div
            class="upload-area" 
            onClick={() => inputRef.current && inputRef.current.click()}
            ref={uploadAreaRef}
        >
            {
                isDragging ?
                "Drop here!" :
                "Drag and drop, paste, or click to upload"
            }
        </div> -->
    {/if}

</div>

<style lang="scss">

    .tab {
        height: 100%;
        display: flex;
        flex-direction: column;
        padding-bottom: 15px;
    }

    .upload-wrap {
        flex: 1;
        width: 100%;
        height: 100%;
        .upload-area {
            background-color: var(--input);
            width: 100%;
            height: 100%;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: var(--text-light);
            transition: .2s box-shadow;
            cursor: pointer;
            &:hover {
                box-shadow: 0 0 0 2px var(--accent-light);
            }
        }
    }

    .by-url-wrap {
        margin-top: 15px;
        .title {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-light);
            margin-bottom: 10px;
            padding-left: 5px;
            text-align: center;
        }
        .input-button {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    }
</style>