import placeholderPlugin from './plugin-placeholder';
import { dropCursor } from 'prosemirror-dropcursor';
import { gapCursor } from 'prosemirror-gapcursor';
import { history } from 'prosemirror-history';
import inputRulesPlugin from './plugin-inputrules';
import keymapPlugins from "./plugin-keymap";
import codemark from 'prosemirror-codemark';
/* import wordCountPlugin from "./wordcount-plugin";
import slashPlugin from "./slash-plugin";
import pasteImagesPlugin from "./paste-images-plugin";
import linkPlugin from "./link-plugin";
import slashTipPlugin from "./plugin-slash-tip"; */
import { columnResizing, tableEditing, goToNextCell } from 'prosemirror-tables';
import marksTooltipPlugin from "./marks-tooltip/plugin-marks-tooltip";
import schema from "../../../../../../lib/prosemirror/schema";
import wordCountPlugin from "./plugin-wordcount";
import slashPlugin from "./slash/plugin-slash";
import slashTipPlugin from "./slash/plugin-slash-tip";

export function getPlugins() {

    return [
        inputRulesPlugin(),
        ...keymapPlugins(),

        placeholderPlugin('Start writing...'),
        marksTooltipPlugin(),
        wordCountPlugin(),
        
        slashPlugin(),
        slashTipPlugin(),

        // from defaults
        dropCursor(),
        gapCursor(),

        history(),

        // pasteImagesPlugin(),
        // linkPlugin(),

        // https://github.com/curvenote/prosemirror-codemark
        ...codemark({ markType: schema.marks.code }),


        columnResizing({ cellMinWidth: 20 }),
        tableEditing(),
    ]

}