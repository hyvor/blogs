<script lang="ts">
	import { goto } from "$app/navigation";
	import { blogStore } from "../../lib/stores/blogStore";

    goto(`/console/${$blogStore.subdomain}/integrations/hyvor-talk`);
</script>