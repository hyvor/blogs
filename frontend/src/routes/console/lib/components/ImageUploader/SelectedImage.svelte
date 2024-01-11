<script lang="ts">
	import { onMount } from "svelte";
    import Cropper from 'cropperjs';
    import 'cropperjs/dist/cropper.css';

    export let url : string;

    let imgEl : HTMLImageElement;
    let cropper : Cropper;

    function handleImageLoad() {

        /* cropper = new Cropper(imgEl, {
            aspectRatio: 16 / 9,
            crop(event) {
                console.log(event.detail.x);
                console.log(event.detail.y);
                console.log(event.detail.width);
                console.log(event.detail.height);
                console.log(event.detail.rotate);
                console.log(event.detail.scaleX);
                console.log(event.detail.scaleY);
            },
        }); */

    }
</script>


<div class="selected-image">
    <div class="img-wrap">
        <img 
            src={url} 
            alt="Editing"
            bind:this={imgEl}
            on:load={handleImageLoad}
        />
    </div>
</div>


<style>
    .selected-image {
        position: absolute;
        z-index: 100;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--box-background);
    }
    .img-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 15px;
    }
    .img-wrap :global(img) {
        display: block;
        max-width: 100%;
    }
    img {
        max-width: 100%;
        max-height: 100%;
    }
</style>