// @ts-ignore
import CodeMirror from 'codemirror';
import { exitCode } from 'prosemirror-commands';
import { undo, redo } from 'prosemirror-history';
import { TextSelection, Selection } from 'prosemirror-state';
import { computeChange } from './nodeview-codeblock';
import { Node } from 'prosemirror-model';
import { EditorView } from 'prosemirror-view';
import schema from '../schema';

export default class CustomHtmlNodeView {
	private cm: CodeMirror.Editor;

	private node: Node;
	private view: EditorView;
	private getPos: () => number;

	public dom: HTMLElement;
	private updating: boolean;
	private incomingChanges: boolean;

	constructor(node: Node, view: EditorView, getPos: () => number | undefined) {
		// Store for later
		this.node = node;
		this.view = view;
		this.getPos = () => getPos() || 0;
		this.incomingChanges = false;

		this.dom = document.createElement('div');
		this.dom.className = 'code-wrap';

		const topbar = document.createElement('div');
		topbar.className = 'topbar';
		topbar.innerHTML = 'Custom HTML/Twig';
		this.dom.appendChild(topbar);

		const codemirrorWrap = document.createElement('div');
		this.dom.appendChild(codemirrorWrap);

		// Create a CodeMirror instance
		this.cm = CodeMirror(codemirrorWrap, {
			value: this.node.textContent,
			lineNumbers: true,
			lineWrapping: true,
			mode: { name: 'twig', base: 'text/html' },
			tabSize: 4,
			indentWithTabs: true,
			indentUnit: 4,
			theme: 'solarized',
			keyMap: 'sublime',
			matchBrackets: true,
			matchTags: { bothTags: true },
			autoCloseBrackets: true,
			autoCloseTags: true,
			extraKeys: this.codeMirrorKeymap()
		});

		const message = document.createElement('div');
		message.className = 'code-toolbar-quit-message';
		message.innerHTML = '<p>SHIFT + Enter to exit</p>';
		message.style.opacity = '0';
		this.cm.getWrapperElement().appendChild(message);

		this.cm.on('focus', () => {
			message.style.opacity = '1';
		});

		this.cm.on('blur', () => {
			message.style.opacity = '0';
		});

		//this.createLanguageSelector();

		// The editor's outer node is our DOM representation
		// this.dom = this.cm.getWrapperElement()
		// CodeMirror needs to be in the DOM to properly initialize, so
		// schedule it to update itself
		setTimeout(() => this.cm.refresh(), 20);

		// This flag is used to avoid an update loop between the outer and
		// inner editor
		this.updating = false;
		// Track whether changes are have been made but not yet propagated
		this.cm.on('beforeChange', () => (this.incomingChanges = true));
		// Propagate updates from the code editor to ProseMirror
		this.cm.on('cursorActivity', () => {
			if (!this.updating && !this.incomingChanges) this.forwardSelection();
		});
		this.cm.on('changes', () => {
			if (!this.updating) {
				this.valueChanged();
				this.forwardSelection();
			}
			this.incomingChanges = false;
		});
		this.cm.on('focus', () => this.forwardSelection());
	}

	forwardSelection() {
		if (!this.cm.hasFocus()) return;
		let state = this.view.state;
		let selection = this.asProseMirrorSelection(state.doc);
		if (!selection.eq(state.selection)) this.view.dispatch(state.tr.setSelection(selection));
	}

	asProseMirrorSelection(doc: Node) {
		let offset = this.getPos() + 1;
		let anchor = this.cm.indexFromPos(this.cm.getCursor('anchor')) + offset;
		let head = this.cm.indexFromPos(this.cm.getCursor('head')) + offset;
		return TextSelection.create(doc, anchor, head);
	}

	setSelection(anchor: number, head: number) {
		this.cm.focus();
		this.updating = true;
		this.cm.setSelection(this.cm.posFromIndex(anchor), this.cm.posFromIndex(head));
		this.updating = false;
	}

	valueChanged() {
		let change = computeChange(this.node.textContent, this.cm.getValue());
		if (change) {
			let start = this.getPos() + 1;
			let tr = this.view.state.tr.replaceWith(
				start + change.from,
				start + change.to,
				change.text ? schema.text(change.text) : []
			);
			this.view.dispatch(tr);
		}
	}

	codeMirrorKeymap() {
		let view = this.view;
		let mod = /Mac/.test(navigator.platform) ? 'Cmd' : 'Ctrl';

		function handleExit() {
			if (exitCode(view.state, view.dispatch)) view.focus();
		}

		return CodeMirror.normalizeKeyMap({
			Up: () => this.maybeEscape('line', -1),
			Left: () => this.maybeEscape('char', -1),
			Down: () => this.maybeEscape('line', 1),
			Right: () => this.maybeEscape('char', 1),
			'Ctrl-Enter': handleExit,
			'Shift-Enter': handleExit,
			[`${mod}-Z`]: () => undo(view.state, view.dispatch),
			[`Shift-${mod}-Z`]: () => redo(view.state, view.dispatch),
			[`${mod}-Y`]: () => redo(view.state, view.dispatch),
			Backspace: () => {
				if (this.cm.getCursor().ch === 0 && this.cm.getValue() === '') {
					let pos = this.getPos();
					let tr = view.state.tr.delete(pos, pos + this.node.nodeSize);
					view.dispatch(tr);
					view.focus();
					return;
				}
				return CodeMirror.Pass;
			}
		});
	}

	maybeEscape(unit: string, dir: number) {
		let pos = this.cm.getCursor();
		if (
			this.cm.somethingSelected() ||
			pos.line != (dir < 0 ? this.cm.firstLine() : this.cm.lastLine()) ||
			(unit == 'char' && pos.ch != (dir < 0 ? 0 : this.cm.getLine(pos.line).length))
		)
			return CodeMirror.Pass;
		this.view.focus();
		let targetPos = this.getPos() + (dir < 0 ? 0 : this.node.nodeSize);
		let selection = Selection.near(this.view.state.doc.resolve(targetPos), dir);
		this.view.dispatch(this.view.state.tr.setSelection(selection).scrollIntoView());
		this.view.focus();
	}

	update(node: Node) {
		if (node.type != this.node.type) return false;

		// console.log(this.node.attrs.language, node.attrs.language)
		if (
			this.node.attrs.language !== node.attrs.language ||
			this.node.attrs.annotations !== node.attrs.annotations ||
			this.node.attrs.name !== node.attrs.name
		) {
			this.node = node;
			// this.updateFromAttrs()
			return true;
		}

		this.node = node;
		let change = computeChange(this.cm.getValue(), node.textContent);
		if (change) {
			this.updating = true;
			this.cm.replaceRange(
				change.text,
				this.cm.posFromIndex(change.from),
				this.cm.posFromIndex(change.to)
			);
			this.updating = false;
		}
		return true;
	}

	selectNode() {
		this.cm.focus();
	}

	stopEvent() {
		return true;
	}
}
