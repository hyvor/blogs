import type { Mark } from "prosemirror-model";
import { Plugin } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";


export default function commentTicksPlugin() {
	return new Plugin({
		view(editorView) {
			return new CommentTicksPlugin(editorView);
		}
	});
}

class CommentTicksPlugin {

    private commentNodes: HTMLSpanElement[] = [];

	constructor(private view: EditorView) {
        setTimeout(() => this.updateTicks(view), 0);
	}

	update(view: EditorView) {
		this.updateTicks(view);
	}

	updateTicks(view: EditorView) {
		
        const doc = view.state.doc;

        let comments: {
            mark: Mark,
            pos: number
        }[] = [];

        view.state.doc.content
            .descendants((node, pos) => {
                if (node.marks.length) {
                    node.marks.forEach(mark => {
                        if (mark.type.name === 'comment') {
                            comments.push({ mark, pos });
                        }
                    });
                }
            }
        );

        // .pm-editor
        const editorParent = view.dom.parentNode as HTMLElement;

        // ensure same number of comment nodes
        if (comments.length !== this.commentNodes.length) {
            
            if (comments.length > this.commentNodes.length) {
                // add new nodes
                for (let i = this.commentNodes.length; i < comments.length; i++) {
                    const node = document.createElement('span');
                    node.className = 'comment-tick';
                    this.commentNodes.push(node);
                    editorParent.appendChild(node);
                }
            } else {
                // remove nodes
                for (let i = comments.length; i < this.commentNodes.length; i++) {
                    const node = this.commentNodes.pop();
                    node?.remove();
                }
            }

        }

        const parentPos = editorParent.getBoundingClientRect();

        // update position
        comments.forEach((comment, i) => {
            const pos = view.coordsAtPos(comment.pos);
            const node = this.commentNodes[i];
            node.style.left = parentPos.left + 'px';

            const top = (pos.top + pos.bottom) / 2;
            node.style.top = top + 'px';
        });

	}
}