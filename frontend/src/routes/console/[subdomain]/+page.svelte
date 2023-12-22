<script lang="ts">
	import { blogStore } from './../lib/stores';
    import { IconButton, Link, Loader } from "@hyvor/design/components";
    import { IconBoxArrowUpRight, IconLaptop, IconTablet } from "@hyvor/icons";

    let type : 'laptop' | 'tablet' = 'laptop';
    let isLoading = true;
</script>

<div class="preview">

    <div class="navi">
        <div class="left">
            <Link href={$blogStore.url} target="_blank" underline={false} color="text">
                {$blogStore.url.replace(/https?:\/\//, '')} <IconBoxArrowUpRight slot="end" size={14} />
            </Link>
        </div>
        <div class="right">
            <IconButton 
                on:click={() => type = 'laptop'}
                color={type == 'laptop' ? "accent" : "invisible"}
            ><IconLaptop /></IconButton>

            <IconButton 
                on:click={() => type = 'tablet'}
                color={type == 'tablet' ? "accent" : "invisible"}
            ><IconTablet /></IconButton>
        </div>
    </div>

    <div 
        class="iframe"
        style="padding: {type === 'laptop' ? 0 : 15}px"
    >
        {#if isLoading}
            <Loader />
        {/if}
        <iframe
            id="preview-iframe"
            src={$blogStore.url}
            style:width={type === 'laptop' ? "100%" : (type === 'tablet' ? 540 : 360) + "px"}
            style:height={type === 'laptop' ? "100%" : 740 + "px"}
            style:display={isLoading ? "none" : "block"}
            on:load={() => isLoading = false}
            title="Preview"
        />
    </div>

</div>


<style>
    .preview {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        border-radius: var(--box-radius);
        background: var(--box-background);
        box-shadow: var(--box-shadow);
    }

    .navi {
        padding: 15px 20px;
        font-size: 16px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }
    .left {    
        flex: 1;
        font-size: 14px;
        font-weight: 600;
    }

    .iframe {
        flex: 1;
        display:flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position:relative;
    }

    iframe {
        max-width: 100%;
        max-height: 100%;
        border: none;
        animation: preview-iframe .5s;
    }
    @keyframes preview-iframe {
        0% {opacity: 0;}
        100% {opacity: 1;}
    }

    @media screen and (max-width: 1200px) {
        .iframe, iframe {
            min-height: 600px;
        }
    }

</style>