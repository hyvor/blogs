import placeholderPlugin from './plugin-placeholder';
import { dropCursor } from 'prosemirror-dropcursor';
import { gapCursor } from 'prosemirror-gapcursor';
import { history } from 'prosemirror-history';
import inputRulesPlugin from './plugin-inputrules';
import keymapPlugins from './plugin-keymap';
import codemark from 'prosemirror-codemark';
import pasteImagesPlugin from './plugin-paste-images';
import { columnResizing, tableEditing } from 'prosemirror-tables';
import marksTooltipPlugin from './marks-tooltip/plugin-marks-tooltip.svelte.js';
import schema from '../schema';
import wordCountPlugin from './plugin-wordcount';
import slashPlugin from './slash/plugin-slash.svelte.js';
import slashTipPlugin from './slash/plugin-slash-tip';
import tableMenuPlugin from './table/plugin-table-menu.svelte.js';
import nodeMenuPlugin from './nodeMenu/plugin-nodemenu.svelte.js';

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

		pasteImagesPlugin(),

		// https://github.com/curvenote/prosemirror-codemark
		...codemark({ markType: schema.marks.code }),

		columnResizing({ cellMinWidth: 20 }),
		tableEditing(),
		tableMenuPlugin(),

		nodeMenuPlugin(),
	];
}
