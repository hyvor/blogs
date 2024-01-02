<script lang="ts">
	import { EditorState } from 'prosemirror-state';
	import schema from "../../../../../lib/prosemirror/schema";
	import { EditorView } from "prosemirror-view";
	import { createEventDispatcher, onMount } from "svelte";
	import { getNodeViews } from "./nodeviews/nodeviews";
	import { importCodemirrorAll } from "../../../../../lib/components/CodemirrorEditor/codemirror";
	import { getPlugins } from "./plugins/plugins";
    export let value: string | null;
    let wrap: HTMLDivElement;

    const dispatch = createEventDispatcher();

    async function createEditor() {

        await importCodemirrorAll();

        const jsonParsedValue = value ? JSON.parse(value) : null;
        wrap.innerHTML = '';

        let state = EditorState.create({
            schema: schema,
            plugins: getPlugins(),
            doc: value ? schema.nodeFromJSON(jsonParsedValue) : undefined
        });

        const view = new EditorView(wrap, {
            state: state,
            nodeViews: getNodeViews(),
            // handleClickOn,
            // handleKeyDown,
            dispatchTransaction: (tr) => {
                dispatch('change', JSON.stringify(tr.doc.toJSON()));

                const state = view.state.apply(tr)
                view.updateState(state)
            },
        });

        return view;

    }

    onMount(() => {
        createEditor();
    })

</script>


<div 
    class="pm-editor"
    bind:this={wrap}
></div>


<style lang="scss">

    .pm-editor {

        --prosemirror-hover-outline: 2px solid #8cf;
        --prosemirror-selected-outline: 3px solid #299af3;


        :global(.ProseMirror) {
            position: relative;
            font-size:18px;
            padding:25px 30px;
            position: relative;
            min-height:620px;
            margin:auto;
            width:700px;
            max-width: 100%;
            word-wrap: break-word;
            white-space: pre-wrap;
            white-space: break-spaces;
            -webkit-font-variant-ligatures: none;
            font-variant-ligatures: none;
            font-feature-settings: "liga" 0;
            &:focus-visible {
                outline: none;
            }
        }

        :global(.ProseMirror > *:first-child) {
            margin-top:0!important;
        }

        :global(blockquote),
        :global(figure),
        :global(h1),
        :global(h2),
        :global(h3),
        :global(h4),
        :global(h5),
        :global(h6),
        :global(p),
        :global(pre),
        :global(ul),
        :global(ol)
        {
            margin: 30px 0 0 0;
        }

        // === NODES
        
        // paragraph
        :global(p) {
            line-height: 30px;
            margin-top: 30px;
            letter-spacing: 0.2px;
        }

        // heading

        :global(.heading-wrap) {
            position: relative;
            :global(div) {
                position: absolute;
                bottom:100%;
                left:0;
                color: var(--text-light);
                font-size:12px;
                margin-bottom: -2px;
                display: flex;
                width:100%;
                align-items: center;
            }
            :global(input) {
                padding: 0;
                background: transparent;
                border: none;
                width: 100%;
                outline:none;
                flex: 1;
                display:block;
                font-family: inherit;
                font-size: inherit;
            }
            :global(+ *) {
                margin-top: 10px;
            }
        }

        :global(h1),
        :global(h2),
        :global(h3),
        :global(h4),
        :global(h5),
        :global(h6)
        {
            margin-top: 35px;
        }

        // hr
        :global(hr) {
            margin: 30px 0;
            border-top: 2px solid var(--grey);
        }

        // blockquote and callout
        :global(blockquote),
        :global(aside)
        {
            margin-top: 30px;
            border-width: 0;
            border-color: #000000;
            border-style: solid;
            border-left-width: 4px;
            padding: 10px 15px;
            :global(*:first-child) {
                margin-top:0;
            }
        }

        // callout
        :global(aside) {
            border-left: none;
            border-radius: 5px;
            display:flex;
            padding:0;
            position:relative;
            :global(.emoji-icon) {
                text-align: center;
                cursor: pointer;
                user-select: none;
                display: inline-block;
                padding: 10px 12px;
            }
            :global(.content-div) {
                flex: 1;
                padding: 10px 10px 10px 0;
            }
            :global(.color-pickers-wrap) {
                position: absolute;
                right: 0;
                bottom: 100%;
            }
            :global(.color-picker) {
                display: inline-block;
                width: 15px;
                height: 15px;
                border-radius: 50%;
                margin-right: 5px;
                cursor: pointer;
                border: 1px solid #aaa;
                position:relative;
                :global(.color-picker-view) {
                    position: absolute;
                    top: 100%;
                    right: 0;
                    margin-top: 4px;
                    width: 200px;
                }
            }
        }

        // figure (embed and image)
        :global(figure) {
            margin-top:45px;

            :global(&:hover) {
                outline: var(--prosemirror-hover-outline);
            }

            :global(figcaption) {
                padding: 7px;
                font-size: 14px;
                text-align: center;
                margin-top: 22px;
            }
            :global(figcaption.empty:before) {
                content: "Enter caption...";
                color: #aaa;
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                pointer-events: none;
            }

            :global(x-embed) {
                position:relative;
                display:block;
                &:before {
                    content: "";
                    position:absolute;
                    z-index:1;
                    width:100%;
                    height:100%;
                    top:0;
                    left:0;
                }
            }
        }

        // img
        :global(img) {
            display: block;
            margin: auto;
            object-fit: cover;
            max-width: 100%;
        }

        // lists
        :global(li) {
            :global(> *) {
                margin:5px 0!important;
            }
        }

        // code block
        :global(.code-wrap) {
            margin-top:30px;
            :global(.code-toolbar) {
                white-space: normal;
                padding: 10px;
                background: var(--input);
                border-radius: 20px 20px 0 0;
                border-bottom: 1px solid #dddddd;
            }
            :global(.code-toolbar-labels) {
                display: flex;
                font-size: 12px;
                :global(div) {
                    flex: 1;
                    padding-left: 4px;
                }
            }
            :global(.code-toolbar-inputs) {
                display: flex;
                :global(input) {
                    flex: 1;
                    min-width: 0;
                    margin-right: 5px;
                    padding: 5px 10px;
                    font-size: 12px;
                    margin-top: 5px;
                    background: #fff;
                    border: none;
                    border-radius: 20px;
                    font-family: inherit;
                }
            }
            
            :global(.CodeMirror) {
                font-size:14px;
                height: initial;
                padding:5px  0;
                padding-bottom: 15px;
                border-radius: 0 0 20px 20px;
                font-family: source-code-pro,Menlo,Courier New,Consolas,monospace!important;
                box-shadow: none!important;
                background-color: var(--input);
            }
            :global(.topbar) {
                background: var(--input);
                border-radius: 20px 20px 0 0;
                border-bottom: 1px solid #dddddd;
                font-size:12px;
                padding: 10px 15px;
            }
            :global(.code-toolbar-quit-message) {
                position: absolute;
                bottom: 0;
                right: 0;
                font-size: 10px;
                padding-right: 10px;
                color: var(--text-light);
            }
        }


        // inline

        :global(:not(pre) > code) {
            background: rgba(135,131,120,0.15);
            color: #EB5757;
            border-radius: 3px;
            font-size: 85%;
            padding: 0.2em 0.4em;
            font-family: monospace;
        }

        :global(a) {
            color: var(--link);
            text-decoration: underline;
        }

        :global(mark) {
            padding: 0.2em 0.4em;
            background-color: #fcf8e3;
        }


        :global(table) {
            margin: 0;
            border: 1px solid black;
            border-collapse: collapse;
            table-layout: fixed;
            white-space: break-spaces;

            :global(tr) {
                height: 20px;
                width: 150px;
            }

            :global(th),
            :global(td) {
                width: 150px;
                height: 40px;
                border: 1px solid #ddd;
                padding: 7px 15px;
                vertical-align: top;
                box-sizing: border-box;
                position: relative;
                :global(p) {
                    margin-top: 0;
                }
            }
            
            :global(th) {
                font-weight: bold;
                text-align: left;
            }
        }

    }


</style>