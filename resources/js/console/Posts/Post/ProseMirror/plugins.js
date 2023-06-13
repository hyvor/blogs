import placeholderPlugin from "./placeholder-plugin";

import { dropCursor } from 'prosemirror-dropcursor';
import { gapCursor } from 'prosemirror-gapcursor';
import { history } from 'prosemirror-history';
import inputRulesPlugin from "./inputrules-plugin";
import keymapPlugins from "./keymap-plugin";
import tooltipPlugin from "./tooltip-plugin";
import wordCountPlugin from "./wordcount-plugin";
import slashPlugin from "./slash-plugin";
import codemark from 'prosemirror-codemark';
import pasteImagesPlugin from "./paste-images-plugin";
import linkPlugin from "./link-plugin";
import slashTipPlugin from "./plugin-slash-tip";
import { columnResizing, tableEditing, goToNextCell } from 'prosemirror-tables';
import { keymap } from 'prosemirror-keymap';

export default function plugins(schema) {

    return [
        inputRulesPlugin(schema),
        //...keymapPlugins(schema),

        placeholderPlugin('Start writing...'),
        tooltipPlugin(schema),
        wordCountPlugin(),
        slashPlugin(schema),
        slashTipPlugin(),

        // from defaults
        dropCursor(),
        gapCursor(),

        history(),

        pasteImagesPlugin(),
        linkPlugin(),

        // https://github.com/curvenote/prosemirror-codemark
        ...codemark({ markType: schema.marks.code }),
    ]

}
