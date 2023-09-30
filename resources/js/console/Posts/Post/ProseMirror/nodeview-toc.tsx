import {EditorView, NodeView} from "prosemirror-view";
import type {Node as ProsemirrorNode, Schema} from 'prosemirror-model';

export default class Toc implements NodeView {

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    schema: Schema;

    dom: HTMLElement;
    contentDOM: HTMLElement;
    mainList: HTMLElement;

    constructor(schema: Schema, node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.schema = schema;

        this.dom = document.createElement('div');
        this.contentDOM = document.createElement('div');
        this.contentDOM.classList.add('toc');

        this.mainList = document.createElement('ul');

        // Put the current heading on top of the stack
        const headingStack: HTMLElement[] = [this.mainList];
        let currentHeadingLevel = 1;
        let displayHeadings = false;
        const docContent = this.view.state.doc.content.content;
        for (let i = 0; i < docContent.length; i++) {
            const node = docContent[i];
            if (node.type.name === 'heading') {
                console.log(headingStack);
                console.log(node.attrs.level);
                if (!displayHeadings) {
                    currentHeadingLevel = node.attrs.level;
                    displayHeadings = true;
                }
                const currentList = headingStack[headingStack.length - 1];
                if (currentHeadingLevel == node.attrs.level) {
                    const newNode = document.createElement('li');
                    newNode.innerHTML = node.textContent;
                    currentList.appendChild(newNode);
                }
                // Smaller heading
                if (node.attrs.level > currentHeadingLevel) {
                    const newList = document.createElement('ul');
                    const newNode = document.createElement('li');
                    newNode.innerHTML = node.textContent;
                    newList.appendChild(newNode);
                    currentList.appendChild(newList);
                    headingStack.push(newList);
                    currentHeadingLevel = node.attrs.level;
                }
                // Bigger heaging
                if (node.attrs.level < currentHeadingLevel) {
                    let lastHeading;
                    while (currentHeadingLevel > node.attrs.level) {
                        lastHeading = headingStack.pop();
                        currentHeadingLevel--;
                    }
                    currentHeadingLevel = node.attrs.level;
                    const newNode = document.createElement('li');
                    newNode.innerHTML = node.textContent;
                    if (headingStack.length == 0) {
                        const newList = document.createElement('ul');
                        if (lastHeading)
                            newList.appendChild(lastHeading);
                        newList.appendChild(newNode);
                        headingStack.push(newList);
                    }
                    else {
                        headingStack[headingStack.length - 1].appendChild(newNode);
                    }
                }
            }
        }
        if (displayHeadings) {
            this.contentDOM.appendChild(headingStack[0]);
            this.dom.appendChild(this.contentDOM);
        }
    
    }

}
