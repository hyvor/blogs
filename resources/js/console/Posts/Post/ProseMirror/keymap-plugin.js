import { keymap } from "prosemirror-keymap";
import {
    clearAndChangeNode,
    baseKeymap,
    chainCommands,
    setBlockType,
} from "./commands";
// import {  } from 'prosemirror-commands'
import { undo, redo } from "prosemirror-history";
import {
    splitListItem,
    sinkListItem,
    liftListItem,
} from "prosemirror-schema-list";
import { NodeSelection, Selection } from "prosemirror-state";

export default function keymapPlugins(schema) {
    var extendedKeymap = {};
    function bind(key, func) {
        extendedKeymap[key] = func;
    }

    var mac =
        typeof navigator != "undefined"
            ? /Mac/.test(navigator.platform)
            : false;

    bind("Mod-z", undo);
    bind("Shift-Mod-z", redo);
    bind("Mod-y", redo);

    // hard break
    const br = schema.nodes.hard_break,
        brCmd = function (state, dispatch) {
            dispatch(
                state.tr.replaceSelectionWith(br.create()).scrollIntoView()
            );
            return true;
        };
    bind("Mod-Enter", brCmd);
    bind("Shift-Enter", brCmd);
    if (mac) bind("Ctrl-Enter", brCmd);

    bind(
        "Backspace",
        chainCommands(
            (state, dispatch) =>
                convertEmptyBlocksToParagraphHandler(state, dispatch, schema),
            figcaptionBackspaceHandler
        )
    );

    const enterAndArrowDown = chainCommands(
        (state, dispatch) => {
            const selection = state.selection;

            if (selection.from !== selection.to)
                // something was selected
                return;

            // If the cursor is in a list item, return false
            const { path } = selection.$to;
            if (path.some(item => item?.type?.name === "list_item"))
                return false;

            /**
             * Code
             * ================
             */

            const parent = selection.$to.parent;
            const text = parent.firstChild?.text;
            let codeMatch;
            if (
                (codeMatch =
                    parent &&
                    parent.type.name === "paragraph" &&
                    text &&
                    text.match(/^```([a-zA-Z0-9+#.]*)$/))
            ) {
                clearAndChangeNode(
                    schema.nodes.code_block.create({
                        language: codeMatch[1],
                    })
                )(state, dispatch);

                return true;
            }
        },
        splitListItem(schema.nodes.list_item),
        figcaptionEnterHandler
    );

    // list item
    bind("Enter", enterAndArrowDown);

    bind("Tab", sinkListItem(schema.nodes.list_item));
    bind("Shift-Tab", liftListItem(schema.nodes.list_item));

    bind("ArrowDown", enterAndArrowDown);

    bind("}", (state, dispatch) => {
        /**
         * Heading IDS
         * ===========
         */
        const selection = state.selection;

        if (selection.from !== selection.to)
            // something was selected
            return;

        const parent = selection.$to.parent;
        const text = parent.firstChild?.text;

        if (parent && parent.type.name === "heading" && text) {
            const match = text.match(/(.+{#([^}\s]+)$)/);
            const spacesMatch = text.match(/\s*{#([^}\s]+)$/);

            if (match) {
                dispatch(
                    state.tr
                        .setNodeMarkup(
                            selection.to - match[1].length - 1,
                            undefined,
                            { ...parent.attrs, id: match[2] }
                        )
                        .replaceWith(
                            selection.to - spacesMatch[0].length,
                            selection.to,
                            ""
                        )
                );
                return true;
            }
        }
    });

    return [keymap(extendedKeymap), keymap(baseKeymap), getCodeBlockKeymap()];
}

function figcaptionEnterHandler(state, dispatch) {
    /**
     * When enter is clicked inside figcaption,
     * we select the parent figure in this function
     * However, this returns fales so that the other functions will run in the command chain
     * So that enter command will run on figure element not figcaption
     * A new paragraph will be created after the figure element
     */

    const { $from } = state.selection;
    if ($from.parent.type.name !== "figcaption") return false;
    if (dispatch)
        dispatch(
            state.tr
                .setSelection(
                    NodeSelection.create(
                        state.doc,
                        $from.pos -
                            $from.parentOffset - // start of figcaption
                            $from.node(-1).nodeSize + // start of figure
                            $from.parent.nodeSize -
                            0
                    )
                )
                .scrollIntoView()
        );
    return true;
}

function figcaptionBackspaceHandler(state, dispatch) {
    const { $from } = state.selection;
    if ($from.parent.type.name !== "figcaption") return false;

    if (!$from.parent?.firstChild.text) return true;
}

function convertEmptyBlocksToParagraphHandler(state, dispatch, schema) {
    let { $from } = state.selection;

    const parent = $from.parent;

    if (!parent) return false;

    const blocks = ["blockquote", "heading", "callout"];

    if (blocks.indexOf(parent.type.name) >= 0) {
        const text = parent.firstChild?.text;

        if (text) return false;

        setBlockType(schema.nodes.paragraph)(state, dispatch);
        return true;
    }

    return false;
}

// https://prosemirror.net/examples/codemirror/
function getCodeBlockKeymap() {
    function arrowHandler(dir) {
        return (state, dispatch, view) => {
            if (state.selection.empty && view.endOfTextblock(dir)) {
                let side = dir == "left" || dir == "up" ? -1 : 1,
                    $head = state.selection.$head;
                let nextPos = Selection.near(
                    state.doc.resolve(
                        side > 0 ? $head.after() : $head.before()
                    ),
                    side
                );
                if (
                    nextPos.$head &&
                    nextPos.$head.parent.type.name == "code_block"
                ) {
                    dispatch(state.tr.setSelection(nextPos));
                    return true;
                }
            }
            return false;
        };
    }

    return keymap({
        ArrowLeft: arrowHandler("left"),
        ArrowRight: arrowHandler("right"),
        ArrowUp: arrowHandler("up"),
        ArrowDown: arrowHandler("down"),
    });
}
