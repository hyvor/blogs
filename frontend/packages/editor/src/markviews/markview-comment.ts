import type { Mark } from "prosemirror-model";
import type { EditorView, MarkView } from "prosemirror-view";


export default class CommentMarkView implements MarkView
{

    dom: HTMLElement;
    contentDOM: HTMLElement;
    mark: Mark;
    view: any;
    getPos: any;

    constructor(mark: Mark, view: EditorView, inline: boolean) {
        this.mark = mark;
        this.view = view;
        
        this.dom = document.createElement(inline ? "span" : "div");
        this.dom.classList.add("user-comment");

        this.contentDOM = document.createElement(inline ? "span" : "div");
        this.dom.appendChild(this.contentDOM);

        this.update();
    }

    update() {
        // 
    }

    destroy() {
        this.dom.remove();
    }

    stopEvent(event: any) {
        return this.view.someProp("handleDOMEvent", (f: any) => f(this.view, event));
    }

    ignoreMutation() {
        return true;
    }


}