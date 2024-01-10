<script lang="ts">
	import { postLanguageStore } from "../../postStore";
	import Editor from "./Editor/Editor.svelte";
	import Title from "./Top/Title.svelte";
	import Top from "./Top/Top.svelte";
</script>

<div 
    id="post-body"
    spellcheck={false}
    dir={$postLanguageStore.direction}
    style="
        dir: {$postLanguageStore.direction};
        text-align: {$postLanguageStore.direction === 'rtl' ? 'right' : 'left'};
    "
>
    <Top />
    <Editor />
</div>

<style>

    #post-body {
        
    }

</style>