<script lang="ts">
	import { goto } from "$app/navigation";
	import { consoleUrlWithBlog } from "../../lib/consoleUrl";

	goto(consoleUrlWithBlog('/integrations/hyvor-talk'), {replaceState: true});
</script>