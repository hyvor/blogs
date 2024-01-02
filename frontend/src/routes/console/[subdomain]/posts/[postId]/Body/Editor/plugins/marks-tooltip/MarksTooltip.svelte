<script lang="ts">
	import type { EditorView } from "prosemirror-view";
	import schema from "../../../../../../../lib/prosemirror/schema";
	import { tick } from "svelte";
	import { IconButton } from "@hyvor/design/components";
	import { IconCode, IconLink45deg, IconTypeBold, IconTypeItalic, IconTypeStrikethrough } from "@hyvor/icons";
	import type { MarkType } from "prosemirror-model";
	import type { EditorState } from "prosemirror-state";
	import { toggleMark } from "prosemirror-commands";
	import LinkSelector from "./LinkSelector/LinkSelector.svelte";

    export let view: EditorView;
    export let show = false;

    let tooltip: HTMLSpanElement;
    let linkSelectorOpen = false;

    function updatePosition() {
        if (!tooltip) return;

        tooltip.style.display = ""
        const {from, to} = view.state.selection

        /**
         * Find the maximum and minimum left points of the current selection
         * Then, the tooltip is placed in the middle of them
         */
        let startLeft = Infinity, endLeft = 0;
        for (let i = from; i <= to; i++) {
            startLeft = Math.min(startLeft, view.coordsAtPos(i).left)
            endLeft = Math.max(endLeft, view.coordsAtPos(i).left)
        }

        // The box in which the tooltip is positioned, to use as base
        let box = tooltip.offsetParent!.getBoundingClientRect()
        // Find a center-ish x position from the selection endpoints (when
        // crossing lines, end may be more to the left)
        let left = (endLeft - startLeft) / 2;
        tooltip.style.left = 
            (startLeft - box.left + left - (tooltip.getBoundingClientRect().width / 2)) + "px"
        tooltip.style.bottom = (box.bottom - view.coordsAtPos(from).top) + "px"
    }

    function isMarkActive(state: EditorState, type: MarkType) {
        const sel = state.selection
        if (sel.empty) return type.isInSet(state.storedMarks || sel.$from.marks())
        else return state.doc.rangeHasMark(sel.from, sel.to, type)
    }


    // position when show/view is changed
    $: if (view && show) {
        (async () => {
            await tick()
            updatePosition()
        })();
    }

    type MarkName = 'link' | 'strong' | 'em' | 'code' | 'strike'

    function getProps(markName: MarkName) {
        const markType = schema.marks[markName]!;
        const isActive = isMarkActive(view.state, markType)
        return {
            size: 'small',
            variant: isActive ? 'fill' : 'invisible',
            color: isActive ? 'accent' : 'gray',
        } as {size: 'small', variant: 'fill' | 'invisible'}
    }

    function handleClick(markName: MarkName) {
        if (markName === 'link') {
            linkSelectorOpen = true;
            return;
        }
        const markType = schema.marks[markName]!;
        toggleMark(markType)(view.state, view.dispatch, view);
        view.focus();
    }

</script>

{#key view}
    {#if show}
        <span 
            class="tooltip"
            bind:this={tooltip}
        >

            <IconButton 
                {...getProps('link')}
                on:click={() => handleClick('link')}
            >
                <IconLink45deg />
            </IconButton>

            <IconButton 
                {...getProps('strong')}
                on:click={() => handleClick('strong')}    
            >
                <IconTypeBold />
            </IconButton>

            <IconButton 
                {...getProps('em')}
                on:click={() => handleClick('em')}
            >
                <IconTypeItalic />
            </IconButton>

            <IconButton 
                {...getProps('code')}
                on:click={() => handleClick('code')} 
            >
                <IconCode />
            </IconButton>

            <IconButton 
                {...getProps('strike')}
                on:click={() => handleClick('strike')}  
            >
                <IconTypeStrikethrough />
            </IconButton>
        </span>
    {/if}
{/key}

{#if linkSelectorOpen}
    <LinkSelector 
        bind:show={linkSelectorOpen}
        {view}
    />
{/if}

<style>

    .tooltip {
        position:absolute;
        background: #fff;
        box-shadow: 0 0 10px rgba(0,0,0,.1);
        border-radius: 20px;
        padding: 5px 10px;
        margin-bottom: 10px;
        z-index: 100;
    }

    .tooltip:after {
        content: "";
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border: 5px solid #fff;
        border-bottom-color: transparent;
        position: absolute;
        border-left-color: transparent;
        border-right-color: transparent;
    }

</style>