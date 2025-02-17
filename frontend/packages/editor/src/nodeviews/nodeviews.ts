import type { NodeViewConstructor } from 'prosemirror-view';
import HeadingNodeView from './nodeview-heading';
import FigcaptionNodeView from './nodeview-figcaption';
import { CalloutNodeView } from './callout/nodeview-callout.svelte.js';
import CodeBlockNodeView from './nodeview-codeblock';
import CustomHtmlNodeView from './nodeview-custom-html';
import EmbedView from './embed/nodeview-embed';
import BookmarkView from './nodeview-bookmark';
import TableNodeView from './table/nodeview-table';
import ImageView from './image/nodeview-image.svelte.js';
import TocView from './toc/nodeview-toc.svelte.js';
import AudioView from './audio/nodeview-audio.svelte.js';

interface NodeViewsType {
	[key: string]: NodeViewConstructor;
}

export function getNodeViews(): NodeViewsType {
	return {
		embed(node, view, getPos) {
			return new EmbedView(node);
		},
		figcaption(node) {
			return new FigcaptionNodeView(node);
		},
		heading(node, view, getPos) {
			return new HeadingNodeView(node, view, getPos);
		},
		callout(node, view, getPos) {
			return new CalloutNodeView(node, view, getPos);
		},
		code_block(node, view, getPos) {
			return new CodeBlockNodeView(node, view, getPos);
		},
		custom_html(node, view, getPos) {
			return new CustomHtmlNodeView(node, view, getPos);
		},
		bookmark(node, view, getPos) {
			return new BookmarkView(node);
		},
		table(node, view, getPos) {
			return new TableNodeView(node, view, getPos);
		},
		image(node, view, getPos) {
			return new ImageView(node, view, getPos);
		},
		audio(node, view, getPos) {
			return new AudioView(node, view, getPos);
		},
		toc(node, view, getPos) {
			return new TocView(node, view, getPos);
		}
	};
}
