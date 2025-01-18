<script lang="ts">
	import type { BlogList } from "../lib/types";

    interface Props {
        blog: BlogList; // const name = blog.subscription ? blog.subscription.plan
    }

    let { blog }: Props = $props();

    
</script>

