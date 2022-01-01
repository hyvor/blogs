
import { keymap } from 'prosemirror-keymap'
import { baseKeymap, chainCommands, exitCode } from 'prosemirror-commands'
import { undo, redo } from 'prosemirror-history'
import { splitListItem } from "prosemirror-schema-list"
import { undoInputRule } from 'prosemirror-inputrules'

export default function keymapPlugins(schema) {

    var extendedKeymap = {};
    function bind(key, func) {
        extendedKeymap[key] = func
    }

    var mac = typeof navigator != "undefined" ? /Mac/.test(navigator.platform) : false;

    bind("Mod-z", undo)
    bind("Shift-Mod-z", redo)
    if (!mac) bind("Mod-y", redo)

    // undo input rule, if it is the last thing user did
    bind("Backspace", undoInputRule)

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
    bind("Enter", splitListItem(schema.nodes.list_item));

    return [
        keymap(extendedKeymap),
        keymap(baseKeymap)
    ]

}