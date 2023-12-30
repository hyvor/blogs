<script lang="ts">
	import { Button } from "@hyvor/design/components";
	import type { Language } from "../../../lib/types";

    export let language: Language;

    function handleCreate() {



    }

</script>


<Button 
    size="small"
    on:click={handleCreate}
>
    Add {language.name} variant
</Button>

<div>
    Create a{['a','e','i','o','u'].indexOf((language.name[0] || '').toLowerCase()) === 0 ? 'n' : ''} {language.name} blog variant to add translations
</div>

<style>
    div {
        margin-top: 5px;
        font-size: 12px;
        color: var(--text-light);
    }
</style>