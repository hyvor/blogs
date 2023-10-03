import {EditorView, NodeView} from "prosemirror-view";
import type {Node as ProsemirrorNode, Schema} from 'prosemirror-model';
import { InfoCircle } from "react-bootstrap-icons";
import React, { useState } from "react";
import ReactDOM from "react-dom/client";

// React component for the heading redirection menu
function HeadingRedirectionMenu({id}: {id: string}) {

    const [idValue, setIdValue] = useState(id);

    return <div className="toc-heading toc-anchor">
        <span>#</span>
        {idValue != null 
            ? 
            <input
                autoFocus={true}
                value={idValue}
                onChange={(event) => {
                    setIdValue(event.target.value);
                }}
                onKeyUp={(event) => {event.stopPropagation();}}
                onKeyDown={(event) => {event.stopPropagation();}}
            /> 
            :
            <InfoCircle 
                className="toc-info-icon"
                onClick={() => setIdValue('')}
            />}
    </div>
}

export default class Toc implements NodeView {

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    schema: Schema;

    dom: HTMLElement;

    createHeading(id: string, text: string) {
        const container = document.createElement('div');
        const headingWrapper = document.createElement('div');

        let root = ReactDOM.createRoot(headingWrapper);
        root.render(<HeadingRedirectionMenu id={id} />);

        const content = document.createElement('span');
        content.innerHTML = text;

        container.appendChild(headingWrapper);
        container.appendChild(content);

        const li = document.createElement('li');
        li.appendChild(container);
        return li;
    }

    genereateTOC(levels: number[]) {
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
                if (!levels.includes(node.attrs.level))
                    continue;
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
        return headingStack;
    }

    createMenu(levels: number[]) {
        const menu = document.createElement('div');
        menu.classList.add('toc-menu');
        for (let i = 0; i < 6; i++) {
            const checkBoxWrapper = document.createElement('div');
            checkBoxWrapper.classList.add('toc-checkbox-wrapper');
            const headerCheckBox = document.createElement('input');
            headerCheckBox.type = 'checkbox';
            headerCheckBox.checked = levels.includes(i + 1);

            headerCheckBox.addEventListener('change', (event) => {
                const target = event.target as HTMLInputElement;
                const levels = this.node.attrs.levels;
                if (target.checked) {
                    levels.push(i + 1);
                }
                else {
                    const index = levels.indexOf(i + 1);
                    levels.splice(index, 1);
                }
                this.loadTOC();
            });

            const label = document.createElement('span');
            label.innerHTML = `H${i + 1}`;
            checkBoxWrapper.appendChild(label);
            checkBoxWrapper.appendChild(headerCheckBox);
            menu.appendChild(checkBoxWrapper);
        }
        return menu;
    }

    loadTOC() {
        const headingStack = this.genereateTOC(this.node.attrs.levels);
        const menu = this.createMenu(this.node.attrs.levels);
        // Clear the current content
        this.dom.innerHTML = '';
        if (headingStack.length > 0) {
            this.dom.appendChild(headingStack[0]);
            this.dom.appendChild(menu);
        }
    }

    constructor(schema: Schema, node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.schema = schema;

        this.dom = document.createElement('div');
        this.dom.classList.add('toc-wrapper');
        this.dom.contentEditable = 'false';

        this.loadTOC();
        
    }

}
