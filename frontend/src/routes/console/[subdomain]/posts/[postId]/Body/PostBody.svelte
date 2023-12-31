<script lang="ts">
	import { postLanguageStore } from "../../postStore";
	import Editor from "./Editor/Editor.svelte";
	import Title from "./Title.svelte";
</script>

<div 
    id="post-body" 
    class="hds-box"
    spellcheck={false}
    dir={$postLanguageStore.direction}
    style="
        dir: {$postLanguageStore.direction};
        text-align: {$postLanguageStore.direction === 'rtl' ? 'right' : 'left'};
    "
>
    <Title />
    <Editor />
</div>

<style>

    #post-body {
        margin-top: 15px;
    }

</style>