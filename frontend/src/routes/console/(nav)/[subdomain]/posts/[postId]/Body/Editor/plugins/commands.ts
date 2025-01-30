import {Node} from "prosemirror-model"
import {TextSelection, NodeSelection, type Command} from "prosemirror-state"
import { undoInputRule } from "prosemirror-inputrules"
import { 
    joinBackward, 
    deleteSelection, 
    selectNodeBackward,
    joinForward,
    selectNodeForward,
    joinUp,
    joinDown,
    lift,
    newlineInCode,
    exitCode,
    createParagraphNear,
    liftEmptyBlock,
    splitBlock,
    splitBlockKeepMarks,
    selectParentNode,
    selectAll,
    wrapIn,
    setBlockType,
    toggleMark,
    autoJoin,
    chainCommands
} from 'prosemirror-commands'; 

export function clearAndChangeNode(node: Node) : Command {
    return function (state, dispatch) {
        let {$from, to} = state.selection, pos
        let same = $from.sharedDepth(to)
        pos = $from.before(same)
        const nodeSel = NodeSelection.create(state.doc, pos);
        const tr = state.tr.replaceWith(nodeSel.from, nodeSel.to, node)
        if (dispatch) {
            dispatch(
                tr.setSelection(TextSelection.create(tr.doc, nodeSel.from + 1))
            )
            return true
        }
        return false
    }
}

let backspace = chainCommands(deleteSelection, undoInputRule, joinBackward, selectNodeBackward)
let del = chainCommands(deleteSelection, undoInputRule, joinForward, selectNodeForward)

// :: Object
// A basic keymap containing bindings not specific to any schema.
// Binds the following keys (when multiple commands are listed, they
// are chained with [`chainCommands`](#commands.chainCommands)):
//
// * **Enter** to `newlineInCode`, `createParagraphNear`, `liftEmptyBlock`, `splitBlock`
// * **Mod-Enter** to `exitCode`
// * **Backspace** and **Mod-Backspace** to `deleteSelection`, `joinBackward`, `selectNodeBackward`
// * **Delete** and **Mod-Delete** to `deleteSelection`, `joinForward`, `selectNodeForward`
// * **Mod-Delete** to `deleteSelection`, `joinForward`, `selectNodeForward`
// * **Mod-a** to `selectAll`
export let pcBaseKeymap = {
    "Enter": chainCommands(newlineInCode, createParagraphNear, liftEmptyBlock, splitBlock),
    "Mod-Enter": exitCode,
    "Backspace": backspace,
    "Mod-Backspace": backspace,
    "Shift-Backspace": backspace,
    "Delete": del,
    "Mod-Delete": del,
    "Mod-a": selectAll
} as {[key: string]: Command}

// :: Object
// A copy of `pcBaseKeymap` that also binds **Ctrl-h** like Backspace,
// **Ctrl-d** like Delete, **Alt-Backspace** like Ctrl-Backspace, and
// **Ctrl-Alt-Backspace**, **Alt-Delete**, and **Alt-d** like
// Ctrl-Delete.
export let macBaseKeymap = {
    "Ctrl-h": pcBaseKeymap["Backspace"],
    "Alt-Backspace": pcBaseKeymap["Mod-Backspace"],
    "Ctrl-d": pcBaseKeymap["Delete"],
    "Ctrl-Alt-Backspace": pcBaseKeymap["Mod-Delete"],
    "Alt-Delete": pcBaseKeymap["Mod-Delete"],
    "Alt-d": pcBaseKeymap["Mod-Delete"]
} as {[key: string]: Command}
// @ts-ignore
for (let key in pcBaseKeymap) macBaseKeymap[key] = pcBaseKeymap[key]

// declare global: os, navigator
const mac = typeof navigator != "undefined" ? /Mac|iP(hone|[oa]d)/.test(navigator.platform)
                    // @ts-ignore
                    : typeof os != "undefined" ? os.platform() == "darwin" : false

// :: Object
// Depending on the detected platform, this will hold
// [`pcBasekeymap`](#commands.pcBaseKeymap) or
// [`macBaseKeymap`](#commands.macBaseKeymap).
export let baseKeymap = mac ? macBaseKeymap : pcBaseKeymap