<script lang="ts">
	import { TextInput } from '@hyvor/design/components';
    import type { EditorView } from 'prosemirror-view';

	export let href: string;
    export let align: string;
    export let size : string;
	export let getPos: () => number | undefined;
	export let view: EditorView;

    let content: string;

</script>

<div class="button-wrap">
    <div class="button-edition-wrap">

    </div>
   
    <a href={href}>
        <input />
    </a>
    
</div>

<style>
	.button-wrap {
		display: flex;
		flex-direction: column;
    }
</style>
