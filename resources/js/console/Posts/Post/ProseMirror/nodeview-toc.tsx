import {EditorView, NodeView} from "prosemirror-view";
import type {Node as ProsemirrorNode, Schema} from 'prosemirror-model';
import { InfoCircle } from "react-bootstrap-icons";
import React, { useEffect, useState } from "react";
import ReactDOM from "react-dom/client";
import Checkbox from "../../../../../../resources/js/console/ReusableComponents/Checkbox";
import { TextSelection } from "prosemirror-state";
import { positionSelectionInMiddleOfScreen } from "./helpers";
import Tooltip from "../../../../../../resources/js/console/ReusableComponents/Tooltip";
import slugify from "../../../../../../resources/js/helpers/slugify";

function focusHeading(id: string, editorView: EditorView) {

    const doc = editorView.state.doc;

    let pos = 0;
    doc.descendants((node, pos2) => {
        if (node.type.name === 'heading' && node.attrs.id === id) {
            pos = pos2;
        }
    });

    const resolvedPos = doc.resolve(pos);
    const selection = TextSelection.create(doc, pos, pos + resolvedPos.nodeAfter!.nodeSize);

    editorView.dispatch(
        editorView.state.tr
            .setSelection(selection)
            .scrollIntoView()
    );
    editorView.focus();

    positionSelectionInMiddleOfScreen(editorView);

}

// React component for the heading redirection menu
function HeadingRedirectionMenu({nodeAttrs, editorView}: {nodeAttrs: {id: string, level: number[]}, editorView: EditorView}) {

    const [saveIdValue, setSaveIdValue] = useState(nodeAttrs.id);

    const doc = editorView.state.doc;

    const modifyId = (newId: string) => {
        let pos = 0;
        doc.descendants((node, pos2) => {
            if (node.type.name === 'heading' && node.attrs.id == saveIdValue) {
                pos = pos2;
            }
        });
        
        editorView.dispatch(
            editorView.state.tr
                .setNodeMarkup(
                    pos,
                    undefined,
                    { ...nodeAttrs, id: slugify(newId) || "" }
                ));
        setSaveIdValue(newId);
    };

    return <div className="toc-heading toc-anchor">
        <span>#</span>
        {saveIdValue != null 
            ? 
            <input
                autoFocus={true}
                value={saveIdValue}
                onChange={(event) => {
                    modifyId(event.target.value);
                }}
                onKeyUp={(event) => {event.stopPropagation();}}
                onKeyDown={(event) => {event.stopPropagation();}}
            /> 
            :
            <Tooltip tooltip={'Heading ID not set'} >
                <InfoCircle 
                    className="toc-info-icon"
                    onClick={() => setSaveIdValue('')}
                />
            </Tooltip>
         }
    </div>
}

export default class Toc implements NodeView {

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    schema: Schema;

    dom: HTMLElement;

    createHeading(nodeAttrs: {id: string, level: number[]}, textContent: string) {
        const container = document.createElement('div');
        const headingWrapper = document.createElement('div');

        const root = ReactDOM.createRoot(headingWrapper);
        root.render(<HeadingRedirectionMenu nodeAttrs={nodeAttrs} editorView={this.view}/>);

        const content = document.createElement('span');
        content.innerHTML = textContent;

        container.appendChild(headingWrapper);
        container.appendChild(content);

        content.addEventListener('click', () => {
            focusHeading(nodeAttrs.id, this.view);
        });

        const li = document.createElement('li');
        li.appendChild(container);

        return li;
    }

    genereateTOC(levels: number[]) {
        const mainList = document.createElement('ul');
        mainList.classList.add('toc-bullet-list');
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
                const newNode = this.createHeading(node.attrs, node.textContent);
                if (currentHeadingLevel == node.attrs.level) {
                    currentList.appendChild(newNode);
                }
                // Smaller heading
                if (node.attrs.level > currentHeadingLevel) {
                    const newList = document.createElement('ul');
                    newList.classList.add('toc-bullet-list');
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

            const root = ReactDOM.createRoot(checkBoxWrapper);
            root.render(
                <Checkbox 
                    label={`H${i + 1}`}
                    checked={levels.includes(i + 1)}
                    onChange={(checked: boolean) => {
                        const levels = this.node.attrs.levels;
                        if (checked) {
                            levels.push(i + 1);
                        }
                        else {
                            const index = levels.indexOf(i + 1);
                            levels.splice(index, 1);
                        }
                        this.loadTOC();
                    }}
                />
            );

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
            const tocList = document.createElement('div');
            tocList.classList.add('toc-list');
            tocList.appendChild(headingStack[0]);
            this.dom.appendChild(tocList);
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

    stopEvent() {
        return true;
    }

}
