<script lang="ts">
	import { Button, TextInput } from "@hyvor/design/components";

    export let src: string | null;
    export let alt: string | null;
    export let width: number | null;
    export let height: number | null;

    let img: HTMLImageElement;

    $: displayWidth = width ? width : img?.naturalWidth;
    $: displayHeight = height ? height : img?.naturalHeight;

    function handleAltInput() {
        console.log("handleAltInput");
    }
</script>

<div class="wrap">
    <div class="top">

        <div class="left">
            <Button size="small">
                Change
            </Button>

            <TextInput 
                size="small"
                placeholder="Add alt text..."
                on:input={handleAltInput}
            >
                <span slot="start">ALT</span>
            </TextInput>
        </div>

        <div class="range-wrap">
            <span class="size">
                Size ({displayWidth} x {displayHeight})
            </span>
            <input 
                type="range"
                min={1}
                max={100}
                step={1}
            />
        </div>

    </div>
    <div class="img-wrap">
        <img
            src={src} 
            alt={alt}
            bind:this={img}
        />
    </div>
</div>

<style>
    .wrap {
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