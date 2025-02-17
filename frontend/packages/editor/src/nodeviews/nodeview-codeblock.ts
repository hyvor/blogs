// @ts-nocheck
import CodeMirror from 'codemirror'; // eslint-disable-line
import { exitCode, joinBackward } from 'prosemirror-commands';
import { undo, redo } from 'prosemirror-history';
import { TextSelection, Selection } from 'prosemirror-state';
import schema from '../schema';
import type { NodeView } from 'prosemirror-view';

// https://prosemirror.net/examples/codemirror/

export default class CodeBlockNodeView implements NodeView {
	dom: HTMLElement;
	contentDOM: HTMLElement;

	constructor(node, view, getPos) {
		// Store for later
		this.node = node;
		this.view = view;
		this.getPos = getPos;
		this.incomingChanges = false;

		this.dom = document.createElement('div');
		this.dom.className = 'code-wrap';

		this.createToolbar();

		this.updateFromAttrs();

		// Append the message after the code-editor

		const codemirrorWrap = document.createElement('div');
		this.dom.appendChild(codemirrorWrap);

		// Create a CodeMirror instance
		this.cm = new CodeMirror(codemirrorWrap, {
			value: this.node.textContent,
			lineNumbers: true,
			lineWrapping: true,
			mode: 'plain',
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
		message.style.opacity = 0;
		this.cm.getWrapperElement().appendChild(message);

		this.cm.on('focus', () => {
			message.style.opacity = 1;
		});

		this.cm.on('blur', () => {
			message.style.opacity = 0;
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

	asProseMirrorSelection(doc) {
		let offset = this.getPos() + 1;
		let anchor = this.cm.indexFromPos(this.cm.getCursor('anchor')) + offset;
		let head = this.cm.indexFromPos(this.cm.getCursor('head')) + offset;
		return TextSelection.create(doc, anchor, head);
	}

	setSelection(anchor, head) {
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
				change.text ? schema.text(change.text) : null
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
			Up: () => this.maybeEscape('line', -1, this.view),
			Left: () => this.maybeEscape('char', -1, this.view),
			Down: () => this.maybeEscape('line', 1, this.view),
			Right: () => this.maybeEscape('char', 1, this.view),
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

	maybeEscape(unit, dir, view) {
		let pos = this.cm.getCursor();
		// When down is pressed on the last line, exit the code block
		if (unit === 'line' && dir === 1 && pos.line === this.cm.lastLine()) {
			if (exitCode(view.state, view.dispatch)) view.focus();
			return;
		}
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

	update(node) {
		if (node.type != this.node.type) return false;

		// console.log(this.node.attrs.language, node.attrs.language)
		if (
			this.node.attrs.language !== node.attrs.language ||
			this.node.attrs.annotations !== node.attrs.annotations ||
			this.node.attrs.name !== node.attrs.name
		) {
			this.node = node;
			this.updateFromAttrs();
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

	createToolbar() {
		const _self = this;
		const toolbar = document.createElement('div');
		toolbar.className = 'code-toolbar';

		const labels = document.createElement('div');
		labels.innerHTML = `
            <div>Language</div>
            <div>Annotations <a 
                href="/docs/syntax-highlighting#annotations" 
                target="_blank"
            ><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
  <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
</svg></a></div>
            <div>File Name</div>
        `;
		labels.className = 'code-toolbar-labels';
		toolbar.appendChild(labels);

		const inputs = document.createElement('div');
		inputs.className = 'code-toolbar-inputs';
		toolbar.appendChild(inputs);

		function createInput(type) {
			const input = document.createElement('input');
			input.className = 'input';
			input.type = 'text';
			input.oninput = (e) => {
				_self.view.dispatch(
					_self.view.state.tr.setNodeMarkup(_self.getPos(), null, {
						..._self.node.attrs,
						[type]: e.target.value
					})
				);
			};
			return inputs.appendChild(input);
		}

		this.inputLang = createInput('language');
		this.inputAno = createInput('annotations');
		this.inputName = createInput('name');

		this.dom.appendChild(toolbar);
	}

	updateFromAttrs() {
		this.inputLang.value = this.node.attrs.language;
		this.inputAno.value = this.node.attrs.annotations;
		this.inputName.value = this.node.attrs.name;
	}
}

export function computeChange(oldVal, newVal) {
	if (oldVal == newVal) return null;
	let start = 0,
		oldEnd = oldVal.length,
		newEnd = newVal.length;
	while (start < oldEnd && oldVal.charCodeAt(start) == newVal.charCodeAt(start)) ++start;
	while (
		oldEnd > start &&
		newEnd > start &&
		oldVal.charCodeAt(oldEnd - 1) == newVal.charCodeAt(newEnd - 1)
	) {
		oldEnd--;
		newEnd--;
	}
	return { from: start, to: oldEnd, text: newVal.slice(start, newEnd) };
}
