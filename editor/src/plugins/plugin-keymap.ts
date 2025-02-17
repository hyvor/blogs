import { keymap } from 'prosemirror-keymap';
import { clearAndChangeNode, baseKeymap } from './commands';
import {
	setBlockType,
	chainCommands,
	toggleMark,
	joinUp,
	joinDown,
	lift,
	selectParentNode,
	wrapIn,
	exitCode
} from 'prosemirror-commands';
import { undo, redo } from 'prosemirror-history';
import { splitListItem, sinkListItem, liftListItem, wrapInList } from 'prosemirror-schema-list';
import {
	type Command,
	EditorState,
	NodeSelection,
	Selection,
	Transaction
} from 'prosemirror-state';
import { ResolvedPos, Schema } from 'prosemirror-model';
import { EditorView } from 'prosemirror-view';
import { undoInputRule } from 'prosemirror-inputrules';
import schema from '../schema';

export default function keymapPlugins() {
	var extendedKeymap: Record<string, Command> = {};

	function bind(key: string, func: Command) {
		extendedKeymap[key] = func;
	}

	var mac = typeof navigator != 'undefined' ? /Mac/.test(navigator.platform) : false;

	bind('Mod-z', undo);
	bind('Shift-Mod-z', redo);
	bind('Mod-y', redo);

	// hard break
	const br = schema.nodes.hard_break!,
		brCmd = function (state, dispatch) {
			dispatch && dispatch(state.tr.replaceSelectionWith(br.create()).scrollIntoView());
			return true;
		} as Command;
	bind('Mod-Enter', brCmd);
	bind('Shift-Enter', brCmd);
	if (mac) bind('Ctrl-Enter', brCmd);

	bind(
		'Backspace',
		chainCommands(
			(state, dispatch) => convertEmptyBlocksToParagraphHandler(state, dispatch, schema),
			figcaptionBackspaceHandler
		)
	);

	function getAllParentsFromSelection(
		state: { doc: { nodeAt: (arg0: any) => any } },
		selection: {
			$from: any;
			$to: any;
		}
	) {
		const selectionStart: ResolvedPos = selection.$from;
		let depth = selectionStart.depth;
		let parent;
		const parentList = [];
		do {
			parent = selectionStart.node(depth);
			if (parent) {
				parentList.push(parent);
				depth--;
			}
		} while (depth > 0 && parent);

		return parentList;
	}

	const commonEnterAndArrowDown: Command = (state, dispatch) => {
		const selection = state.selection;

		if (selection.from !== selection.to)
			// something was selected
			return false;

		const parents = getAllParentsFromSelection(state, selection);
		if (parents.some((item) => item?.type?.name === 'list_item')) return false;

		/**
		 * Code
		 * ================
		 */

		const parent = selection.$to.parent;
		const text = parent.firstChild?.text;
		let codeMatch;
		if (
			(codeMatch =
				parent && parent.type.name === 'paragraph' && text && text.match(/^```([a-zA-Z0-9+#.]*)$/))
		) {
			clearAndChangeNode(
				schema.nodes.code_block!.create({
					language: codeMatch[1]
				})
			)(state, dispatch);

			return true;
		}

		return false;
	};

	const enterBehavior = chainCommands(
		commonEnterAndArrowDown,
		splitListItem(schema.nodes.list_item!),
		figcaptionEnterHandler
	);

	const downArrowBehavior = chainCommands((state, dispatch) => {
		const selection = state.selection;

		if (selection.from !== selection.to)
			// something was selected
			return false;

		// If the cursor is in a list item, return false
		// @ts-ignore (TODO: fix this)
		const { path } = selection.$to;
		// @ts-ignore (TODO: fix this)
		if (path.some((item) => item?.type?.name === 'table_cell')) {
			// Get the next node
			const tableCell = selection.$from.node(-1);
			const table = selection.$from.node(-3);
			const tablePos = selection.$from.before(-3);
			const nextNode = selection.$to;
			const nextNodeExpctedPos = tablePos + table.nodeSize;
			const nodeAtPos = state.doc.nodeAt(nextNodeExpctedPos);
			if (nextNode.pos + tableCell.nodeSize >= nextNodeExpctedPos && nodeAtPos == null) {
				const { $from } = state.selection;
				const tr = state.tr.insert($from.after(-1), schema.nodes.paragraph!.create());
				if (dispatch) dispatch(tr.setSelection(Selection.near(tr.doc.resolve($from.after(-1)))));
				return true;
			}
		}
		return commonEnterAndArrowDown(state, dispatch);
	}, figcaptionEnterHandler);

	// list item
	bind('Enter', enterBehavior);

	bind('Tab', sinkListItem(schema.nodes.list_item!));
	bind('Shift-Tab', liftListItem(schema.nodes.list_item!));

	bind('ArrowDown', downArrowBehavior);

	bind('}', (state, dispatch) => {
		/**
		 * Heading IDS
		 * ===========
		 */
		const selection = state.selection;

		if (selection.from !== selection.to)
			// something was selected
			return false;

		const parent = selection.$to.parent;
		const text = parent.firstChild?.text;

		if (parent && parent.type.name === 'heading' && text) {
			const match = text.match(/(.+{#([^}\s]+)$)/);
			const spacesMatch = text.match(/\s*{#([^}\s]+)$/);

			if (match && dispatch) {
				dispatch(
					state.tr
						.setNodeMarkup(selection.to - match[1]!.length - 1, undefined, {
							...parent.attrs,
							id: match[2]
						})
						.replaceWith(selection.to - (spacesMatch ? spacesMatch[0].length : 0), selection.to, [])
				);
				return true;
			}
		}

		return false;
	});

	return [
		keymap(extendedKeymap),
		keymap(baseKeymap),
		getCodeBlockKeymap(),
		getShortcutKeymap(schema)
	];
}

const figcaptionEnterHandler: Command = (state, dispatch) => {
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
};

const figcaptionBackspaceHandler: Command = (state, dispatch) => {
	const { $from } = state.selection;
	if ($from.parent.type.name !== 'figcaption') return false;
	if (!$from.parent?.firstChild?.text) return true;
	return false;
};

function convertEmptyBlocksToParagraphHandler(
	state: EditorState,
	dispatch: EditorView['dispatch'] | undefined,
	schema: Schema
): boolean {
	let { $from } = state.selection;

	const parent = $from.parent;

	if (!parent) return false;

	const blocks = ['blockquote', 'heading', 'callout'];

	if (blocks.indexOf(parent.type.name) >= 0) {
		const text = parent.firstChild?.text;

		if (text) return false;

		setBlockType(schema.nodes.paragraph!)(state, dispatch);
		return true;
	}

	return false;
}

// https://prosemirror.net/examples/codemirror/
function getCodeBlockKeymap() {
	function arrowHandler(dir: 'left' | 'right' | 'up' | 'down') {
		return ((state, dispatch, view) => {
			if (state.selection.empty && view && view.endOfTextblock(dir)) {
				let side = dir == 'left' || dir == 'up' ? -1 : 1,
					$head = state.selection.$head;
				let nextPos = Selection.near(
					state.doc.resolve(side > 0 ? $head.after() : $head.before()),
					side
				);
				if (nextPos.$head && nextPos.$head.parent.type.name == 'code_block' && dispatch) {
					dispatch(state.tr.setSelection(nextPos));
					return true;
				}
			}
			return false;
		}) as Command;
	}

	return keymap({
		ArrowLeft: arrowHandler('left'),
		ArrowRight: arrowHandler('right'),
		ArrowUp: arrowHandler('up'),
		ArrowDown: arrowHandler('down')
	});
}

function getShortcutKeymap(schema: Schema) {
	const userAgent = window.navigator.userAgent.toLowerCase();
	const isMac = /macintosh|mac os x/i.test(userAgent);
	let keyMap = {
		'Mod-z': undo,
		'Shift-Mod-z': redo,
		Backspace: undoInputRule
	};
	if (!isMac) keyMap = { ...keyMap, ...{ 'Mod-y': redo } };

	keyMap = {
		...keyMap,
		...{
			'Alt-ArrowUp': joinUp,
			'Alt-ArrowDown': joinDown,
			'Mod-[': lift,
			Escape: selectParentNode
		}
	};

	if (schema.marks.strong)
		keyMap = {
			...keyMap,
			...{
				'Mod-b': toggleMark(schema.marks.strong),
				'Mod-B': toggleMark(schema.marks.strong)
			}
		};
	if (schema.marks.em)
		keyMap = {
			...keyMap,
			...{
				'Mod-i': toggleMark(schema.marks.em),
				'Mod-I': toggleMark(schema.marks.em)
			}
		};
	if (schema.marks.code)
		keyMap = {
			...keyMap,
			...{ 'Mod-`': toggleMark(schema.marks.code) }
		};
	if (schema.nodes.bullet_list)
		keyMap = {
			...keyMap,
			...{ 'Shift-Ctrl-8': wrapInList(schema.nodes.bullet_list) }
		};
	if (schema.nodes.ordered_list)
		keyMap = {
			...keyMap,
			...{ 'Shift-Ctrl-9': wrapInList(schema.nodes.ordered_list) }
		};
	if (schema.nodes.blockquote)
		keyMap = {
			...keyMap,
			...{ 'Ctrl->': wrapIn(schema.nodes.blockquote) }
		};
	if (schema.nodes.hard_break) {
		let cmd = chainCommands(exitCode, (state, dispatch) => {
			if (!dispatch) return false;
			dispatch(state.tr.replaceSelectionWith(schema.nodes.hard_break!.create()).scrollIntoView());
			return true;
		});

		keyMap = { ...keyMap, ...{ 'Mod-Enter': cmd, 'Shift-Enter': cmd } };
		if (isMac) keyMap = { ...keyMap, ...{ 'Ctrl-Enter': cmd } };
	}
	if (schema.nodes.list_item)
		keyMap = {
			...keyMap,
			...{
				Enter: splitListItem(schema.nodes.list_item)
			}
		};
	if (schema.nodes.paragraph)
		keyMap = {
			...keyMap,
			...{ 'Shift-Ctrl-0': setBlockType(schema.nodes.paragraph) }
		};
	if (schema.nodes.code_block)
		keyMap = {
			...keyMap,
			...{ 'Shift-Ctrl-\\': setBlockType(schema.nodes.code_block) }
		};
	if (schema.nodes.heading) {
		const headingLevels = Array.from({ length: 6 }, (_, i) => ++i);
		const headingMap = headingLevels.reduce(
			(acc, level) => ({
				...acc,
				...{
					[`Shift-Ctrl-${level}`]: setBlockType(schema.nodes.heading!, { level })
				}
			}),
			{}
		);

		keyMap = { ...keyMap, ...headingMap };
	}

	if (schema.nodes.horizontal_rule)
		keyMap = {
			...keyMap,
			...{
				'Mod-_': (state: EditorState, dispatch: (tr: Transaction) => void) => {
					dispatch(
						state.tr.replaceSelectionWith(schema.nodes.horizontal_rule!.create()).scrollIntoView()
					);
					return true;
				}
			}
		};

	return keymap(keyMap);
}
