import { marked } from "marked";
// @ts-ignore
import DOMPurify from 'dompurify';
import type { EditorView } from "prosemirror-view";
import schema from "../../../../../../lib/prosemirror/schema";
import { TextSelection } from "prosemirror-state";
import { DOMParser, Node } from "prosemirror-model";

export function getHtmlFromMarkdownResponse(response: string | null) {
    if (!response) return '';
    const renderer = new marked.Renderer();
    renderer.link = function(href, title, text) {
        return `<a href="${href}" target="_blank" rel="noopener noreferrer">${text}</a>`;
    }
    return DOMPurify.sanitize(marked(response || '', {renderer}) as string);
}

export function copyHtmlToClipboard(el: HTMLElement) {

    
    window.getSelection()?.removeAllRanges();
    let range = document.createRange();
    range.selectNode(el);
    window.getSelection()?.addRange(range);
    document.execCommand('copy');
    window.getSelection()?.removeAllRanges();

}

export function appendHtml(view: EditorView, html: string) {

    const div = document.createElement('div');
    div.innerHTML = html;
    const node = DOMParser.fromSchema(schema).parse(div);

    const tr = view.state.tr;
    
    tr
        .insert(view.state.selection.from, node)
        .setSelection(
            new TextSelection(tr.doc.resolve(view.state.selection.from + node.nodeSize - 1))
        )

    view.dispatch(tr);
    view.focus();

}