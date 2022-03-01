
import { keymap } from 'prosemirror-keymap'
import { baseKeymap, chainCommands, exitCode, liftFigcaption, selectNodeBackward } from './commands'
import { undo, redo } from 'prosemirror-history'
import { splitListItem } from "./list"
import { NodeSelection, SelectionRange } from 'prosemirror-state'
import { createRich } from './creators'

export default function keymapPlugins(schema) {

    var extendedKeymap = {};
    function bind(key, func) {
        extendedKeymap[key] = func
    }

    var mac = typeof navigator != "undefined" ? /Mac/.test(navigator.platform) : false;

    bind("Mod-z", undo)
    bind("Shift-Mod-z", redo)
    bind("Mod-y", redo)

    // hard break    
    const 
        br = schema.nodes.hard_break, 
        cmd = chainCommands(
            exitCode, 
            function (state, dispatch) {
                dispatch(state.tr.replaceSelectionWith(br.create()).scrollIntoView())
                return true
            });
    bind("Mod-Enter", cmd)
    bind("Shift-Enter", cmd)
    if (mac) bind("Ctrl-Enter", cmd)

    // list item
    bind("Enter", chainCommands(
        (state, dispatch) => {
            const selection = state.selection;

            if (selection.from !== selection.to) // something was selected
                return;

            /**
             * RICH
             * ===================
             */
            const parent = selection.$to.parent;
            const url = parent.firstChild?.text;
            if (
                parent &&
                parent.type.name === 'paragraph' && 
                url &&
                url.match(
                    /^https:\/\/[^\s]+$/i
                )
            ) {

                // code from promirror-command -> selectParentNode()
                // to get the from and to of the paragraph
                // no idea how it works 
                let {$from, to} = state.selection, pos
                let same = $from.sharedDepth(to)
                pos = $from.before(same)
                const nodeSel = NodeSelection.create(state.doc, pos);

                dispatch(
                    state.tr.replaceWith(nodeSel.from, nodeSel.to, createRich(schema, url))
                )
                return true;
            }

        },
        splitListItem(schema.nodes.list_item),
        figcaptionHandler
    ));

    bind("}", (state, dispatch) => {

        /**
         * Heading IDS
         * ===========
         */
        const selection = state.selection

        if (selection.from !== selection.to) // something was selected
            return;

        const parent = selection.$to.parent;
        const text = parent.firstChild?.text;

        if (
            parent &&
            parent.type.name === 'heading' &&
            text
        ) {

            const match = text.match(/(.+{#([^}\s]+)$)/)
            const spacesMatch = text.match(/\s*{#([^}\s]+)$/)

            if (match) {
                dispatch(
                    state.tr
                        .setNodeMarkup(
                                selection.to - match[1].length - 1, 
                                undefined, 
                                {...parent.attrs, id: match[2]}
                        )
                        .replaceWith(selection.to - spacesMatch[0].length, selection.to, "")
                )
                return true;
            }
            
        }
        
    })

    return [
        keymap(extendedKeymap),
        keymap(baseKeymap),
    ]

}

function figcaptionHandler(state, dispatch) {
    /**
     * When enter is clicked inside figcaption,
     * we select the parent figure in this function
     * However, this returns fales so that the other functions will run in the command chain
     * So that enter command will run on figure element not figcaption
     * A new paragraph will be created after the figure element
     */

    const { $from } = state.selection;
    if ($from.parent.type.name !== 'figcaption') return false;
    if (dispatch)
        dispatch(state.tr
            .setSelection(
                NodeSelection.create(state.doc, 
                    $from.pos -
                    $from.parentOffset - // start of figcaption
                    $from.node(-1).nodeSize + // start of figure
                    $from.parent.nodeSize -
                    0
                )
            )
            .scrollIntoView()
        )
    return false
}