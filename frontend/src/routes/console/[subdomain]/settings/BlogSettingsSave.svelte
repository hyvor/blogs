<script lang="ts">
	import { Button } from "@hyvor/design/components";
import { blogOriginalStore, blogStore } from "../../lib/stores/blogStore";
	import type { Blog, BlogVariant } from "../../lib/types";

    export let keys : (keyof Blog)[] = [];
    export let variantKeys : (keyof BlogVariant)[] = [];

    $: should = getShouldSave($blogStore, $blogOriginalStore);

    function getShouldSave(blog: Blog, blogOriginal: Blog) {

        if (keys.some(key => blog[key] !== blogOriginal[key])) {
            return true;
        }

        if (
            blog.variants.some(
                (variant, i) => 
                    variantKeys.some(key => variant[key] !== blogOriginal.variants[i]![key])
                )
        ) {
            return true;
        }

        return false;
    }

</script>

<div class="save">

    <Button color="invisible">
        Discard
    </Button>

    <Button>
        Save
    </Button>

</div>

<style>
    .save {
        padding: 15px 30px;
    }
</style>