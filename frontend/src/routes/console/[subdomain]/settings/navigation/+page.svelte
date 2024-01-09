<script>
	import { Button } from "@hyvor/design/components";
	import SettingsTop from "../@components/SettingsTop.svelte";
	import { IconPlus } from "@hyvor/icons";

    let isCreating = false;

</script>


<SettingsTop>

    <Button on:click={() => isCreating = true}>
        Create Navigation <IconPlus slot="end" />
    </Button>

</SettingsTop>