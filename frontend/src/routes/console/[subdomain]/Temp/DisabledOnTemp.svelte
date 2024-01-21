<script>
	import { IconMessage } from "@hyvor/design/components";
import { isTempStore } from "../../lib/temp";
	import { IconLock } from "@hyvor/icons";

</script>


<div class="wrap">

    <slot />

    {#if $isTempStore}
        <div class="temp-notice">
            <div class="overlay"></div>
            <IconMessage 
                message="This feature is disabled on temporary blogs."
                icon={IconLock}
                iconSize={50}
            />
        </div>
    {/if}    

</div>


<style>
    .wrap {
        position: relative;
        height: 100%;
        flex: 1;
        pointer-events: none;
    }
    .temp-notice {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 10;
    }
    .overlay {
        position:absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
        background-color: #fff;
        opacity: 0.8;
        border-radius: 20px;
    }
</style>