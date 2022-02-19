import placeholderPlugin from "./placeholder-plugin";

import { dropCursor } from 'prosemirror-dropcursor';
import { gapCursor } from 'prosemirror-gapcursor';
import { history } from 'prosemirror-history';
import inputRulesPlugin from "./inputrules-plugin";
import keymapPlugins from "./keymap-plugin";
import tooltipPlugin from "./tooltip-plugin";
import navigatorPlugin from "./navigator-plugin";
import wordCountPlugin from "./wordcount-plugin";

export default function plugins(schema) {

    return [
        inputRulesPlugin(schema),
        ...keymapPlugins(schema),

        placeholderPlugin('Start writing...'),
        tooltipPlugin(schema),
        navigatorPlugin(),
        wordCountPlugin(),

        // from defaults
        dropCursor(),
        gapCursor(),

        history()
    ]

}