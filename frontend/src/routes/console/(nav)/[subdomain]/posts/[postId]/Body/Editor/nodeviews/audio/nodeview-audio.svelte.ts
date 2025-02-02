import type { Node } from 'prosemirror-model';
import type { EditorView, NodeView } from 'prosemirror-view';
import AudioNodeView from './AudioNodeView.svelte';
import { mount } from 'svelte';

export default class AudioView implements NodeView {
	private node: Node;
	private view: EditorView;
	private getPos: () => number | undefined;

	public dom: HTMLDivElement;

	private src = $state('');

	constructor(node: Node, view: EditorView, getPos: () => number | undefined) {
		this.node = node;
		this.view = view;
		this.getPos = getPos;

		this.dom = document.createElement('div');
		this.src = node.attrs.src;

		mount(AudioNodeView, {
			target: this.dom,
			props: {
				src: this.src,
				getPos: this.getPos,
				view: this.view
			}
		});
	}

	update(node: Node) {
		if (node.type.name === 'audio') {
			this.src = node.attrs.src;
			return true;
		}
		return false;
	}

	stopEvent() {
		return false;
	}
}
