import React, { useEffect, useRef } from 'react';
import { EditorState, EditorStateConfig, NodeSelection } from "prosemirror-state"
import type { Node as ProsemirrorNode } from 'prosemirror-model'

import HBSchema from './schema';
import plugins from './plugins';
import Figcaption from './nodeview-figcaption';
import Heading from './nodeview-heading';
import Callout from './Callout/nodeview-callout';
import CodeBlock from './nodeview-codeblock';
import Image from './Image/nodeview-image';
import Bookmark from './nodeview-bookmark';
import CustomHtml from "./nodeview-custom-html";
import EmbedView from "./nodeview-embed";
import { EditorView, NodeViewConstructor } from "prosemirror-view";
import useUpdateEffect from "../../../../helpers/hooks/useUpdateEffect";
import Table from './nodeview-table';
import { tableEditing, columnResizing, goToNextCell, fixTables } from 'prosemirror-tables';
import { keymap } from 'prosemirror-keymap';
import { usePostActions } from '../helpers';
import Toc from './nodeview-toc';

function getState(val: string) {
    val = val ? JSON.parse(val) : null
    const newState: EditorStateConfig = {
        schema: HBSchema,
        plugins: plugins(HBSchema),
    }
    if (val) {
        newState.doc = HBSchema.nodeFromJSON(val);
    }
    return newState;
}

interface NodeViewsType {
    [key: string]: NodeViewConstructor
}

const nodeViews: NodeViewsType = {
    embed(node, view, getPos) {
        return new EmbedView(HBSchema, node, view, getPos);
    },
    figcaption(node) {
        return new Figcaption(node);
    },
    heading(node, view, getPos) {
        return new Heading(node, view, getPos);
    },
    callout(node, view, getPos) {
        return new Callout(node, view, getPos)
    },
    code_block(node, view, getPos) {
        return new CodeBlock(node, view, getPos)
    },
    custom_html(node, view, getPos) {
        return new CustomHtml(node, view, getPos)
    },
    image(node, view, getPos) {
        return new Image(HBSchema, node, view, getPos)
    },
    bookmark(node, view, getPos) {
        return new Bookmark(node, view, getPos)
    },
    table(node, view, getPos) {
        return new Table(HBSchema, node, view, getPos);
    },
    toc(node, view, getPos) {
        return new Toc(HBSchema, node, view, getPos);
    },
}

interface EditorProps {
    id: number,
    value: string,
    currentLanguageId: number,
    status: string,
    onChange: (val: string) => void,
    version: number,
}

export default function Editor({ id, currentLanguageId, status, value, onChange, version }: EditorProps) {

    const { changeEditorState } = usePostActions(id);

    const editorRef = useRef<null | HTMLDivElement>(null)
    const mounted = useRef(false)

    function createEditor() {

        const jsonParsedValue = value ? JSON.parse(value) : null;

        (editorRef.current as HTMLDivElement).innerHTML = "";

        let state = EditorState.create({
            schema: HBSchema,
            plugins: plugins(HBSchema),
            doc: value ? HBSchema.nodeFromJSON(jsonParsedValue) : undefined
        });
        const fix = fixTables(state);
        if (fix) 
            state = state.apply(fix.setMeta('addToHistory', false));


        const view = new EditorView(editorRef.current, {
            state: state,
            nodeViews,
            handleClickOn,
            handleKeyDown,
            dispatchTransaction: (tr) => {
                onChange(JSON.stringify(tr.doc.toJSON()))

                const state = view.state.apply(tr)
                view.updateState(state)
            },
        });

        return view;

    }

    useEffect(() => {

        if (mounted.current)
            return;

        mounted.current = true;
        const view = createEditor()

        changeEditorState('editorView', view);

        return () => view.destroy();
    }, []);

    /**
     * Re-create the editor when the post ID or current language ID changes
     */
    useUpdateEffect(() => {
        const view = createEditor()
        return () => view.destroy();
    }, [id, currentLanguageId, status, version])

    return <div className="prosemirror-editor-wrap" ref={editorRef} />

}

function handleClickOn(view: EditorView, pos: number, node: ProsemirrorNode, posBefore: number, e: MouseEvent) {
    // if (pos != posBefore) return false
    //return false;

    /**
     * Select figure when clicking on it
     */
    if (node.type.name === "figure" && (e.target as Node)?.nodeName !== "INPUT") {
        const resolvedPos = view.state.doc.resolve(pos)
        if (resolvedPos.parent.type.name === 'figcaption')
            return false;

        const tr = view.state.tr;
        const resolvedPosBefore = view.state.doc.resolve(posBefore);
        const nodeSel = new NodeSelection(resolvedPosBefore)
        view.dispatch(tr.setSelection(nodeSel))
        return true;
    }
}

// prevent tab-key browser navigation
function handleKeyDown(/*view, e*/) {
    // if (e.key === 'Tab') {
    //    // e.preventDefault();
    // }
}