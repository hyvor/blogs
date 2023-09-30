import {EditorView, NodeView} from "prosemirror-view";
import type {Node as ProsemirrorNode, Schema} from 'prosemirror-model';
import { InfoCircle } from "react-bootstrap-icons";
import React from "react";
import ReactDOM from "react-dom/client";

export default class Toc implements NodeView {

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    schema: Schema;

    dom: HTMLElement;

    createHeading(id: string, text: string) {
        const container = document.createElement('div');
        container.contentEditable = 'false';
        container.classList.add('toc-heading');
        const anchor = document.createElement('span');
        anchor.classList.add('toc-anchor');
        
        if (id) {
            anchor.innerHTML = `#${id}`;
        }
        else {
           let root = ReactDOM.createRoot(anchor);
            root.render(
                <InfoCircle />
            );
        }

        const content = document.createElement('span');
        content.innerHTML = text;
        container.appendChild(anchor);
        console.log(anchor);
        container.appendChild(content);

        const li = document.createElement('li');
        li.appendChild(container);
        return li;
    }

    constructor(schema: Schema, node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.schema = schema;

        this.dom = document.createElement('div');
        this.dom.contentEditable = 'false';

        const mainList = document.createElement('ul');
        mainList.contentEditable = 'false';

        // Put the current heading on top of the stack
        const headingStack: HTMLElement[] = [mainList];
        let currentHeadingLevel = 1;
        let displayHeadings = false;
        const docContent = this.view.state.doc.content.content;
        for (let i = 0; i < docContent.length; i++) {
            const node = docContent[i];
            if (node.type.name === 'heading') {
                if (!displayHeadings) {
                    currentHeadingLevel = node.attrs.level;
                    displayHeadings = true;
                }
                const currentList = headingStack[headingStack.length - 1];
                const newNode = this.createHeading(node.attrs.id, node.textContent);
                if (currentHeadingLevel == node.attrs.level) {
                    currentList.appendChild(newNode);
                }
                // Smaller heading
                if (node.attrs.level > currentHeadingLevel) {
                    const newList = document.createElement('ul');
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
            this.dom.appendChild(headingStack[0]);
        }
    
    }

}
