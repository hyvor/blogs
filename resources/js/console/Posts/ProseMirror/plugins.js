import placeholderPlugin from "./placeholder-plugin";

import { dropCursor } from 'prosemirror-dropcursor';
import { gapCursor } from 'prosemirror-gapcursor';
import { history } from 'prosemirror-history';
import inputRulesPlugin from "./inputrules-plugin";
import keymapPlugins from "./keymap-plugin";
import tooltipPlugin from "./tooltip-plugin";

export default function plugins(schema) {

    return [
        inputRulesPlugin(schema),
        ...keymapPlugins(schema),

        placeholderPlugin('Start writing...'),
        tooltipPlugin(schema),

        // from defaults
        dropCursor(),
        gapCursor(),

        history()
    ]

}