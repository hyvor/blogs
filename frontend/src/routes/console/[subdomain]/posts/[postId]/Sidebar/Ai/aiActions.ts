import type { GptPrompt, Post, PostVariant } from "../../../../../lib/types";
import consoleApi from "../../../../../lib/consoleApi";
import type { EditorView } from "prosemirror-view";
import schema from "../../../../../lib/prosemirror/schema";
import { TextSelection } from "prosemirror-state";
import { DOMParser, Node } from "prosemirror-model";

export function sendPrompt(prompt: string, post_id: number) {
    return consoleApi.post({
        endpoint: '/gpt/prompt',
        data: {
            post_id: post_id,
            prompt: prompt
        }
    });
}

export function getPrompts(post_id: number) {
    return consoleApi.get<GptPrompt[]>({
        endpoint: '/gpt/post-history',
        data: {
            post_id
        }
    });
}

export function resetChat(post_id: number) {
    return consoleApi.delete({
        endpoint: '/gpt/post-history',
        data: {
            post_id
        }
    });
}

export function appendHtml(view: EditorView, html: string) {

    const div = document.createElement('div');
    div.innerHTML = html;
    const node = DOMParser.fromSchema(schema).parse(div);
    console.log(node);

    const tr = view.state.tr;
    
    tr
        .insert(view.state.selection.from, node)
        .setSelection(
            new TextSelection(tr.doc.resolve(view.state.selection.from + node.nodeSize - 1))
        )

    view.dispatch(tr);
    view.focus();

}