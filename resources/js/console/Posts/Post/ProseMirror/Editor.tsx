import React, { useState } from 'react';
import {EditorState, EditorStateConfig, NodeSelection} from "prosemirror-state"
import type {Node as ProsemirrorNode} from 'prosemirror-model'

import HBSchema from './schema';
import plugins from './plugins';

/**
 * 
 */
import {ProseMirror} from 'use-prosemirror';
import useUpdateEffect from '../../../../helpers/hooks/useUpdateEffect';
import Figcaption from './nodeview-figcaption';
import Heading from './nodeview-heading';
import Callout from './Callout/nodeview-callout';
import CodeBlock from './nodeview-codeblock';
import Image from './Image/nodeview-image';
import Bookmark from './nodeview-bookmark';
import CustomHtml from "./nodeview-custom-html";
import EmbedView from "./nodeview-embed";
import {EditorView, NodeViewConstructor} from "prosemirror-view";

function getState(val: string) {
    val = val ? JSON.parse(val) : null
    const newState: EditorStateConfig = {
        schema: HBSchema,
        plugins: plugins(HBSchema),
    }
    if (val) {
        newState.doc = HBSchema.nodeFromJSON( val );
    }
    return () => EditorState.create(newState);
}

interface NodeViewsType {
    [key: string]: NodeViewConstructor
}

const nodeViews : NodeViewsType = {
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
    }
}

interface EditorProps {
    id: number,
    value: string,
    currentLanguageId: number,
    onChange: (val: string) => void,
    editable: boolean
}


export default function Editor(props: EditorProps) {

    const [state, setState] = useState(getState(props.value));

    useUpdateEffect(() => {
        setState(getState(props.value))
    }, [props.id, props.currentLanguageId]);

    function handleChange(state: EditorState) {
        props.onChange(JSON.stringify(state.doc.toJSON()));
        setState(state);
    }

    return <ProseMirror 
        state={state}
        nodeViews={nodeViews}
        onChange={handleChange}
        handleClickOn={handleClickOn}
        /*handleKeyDown={handleKeyDown}*/
        editable={() => props.editable}
    />
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
/*function handleKeyDown(view, e) {
    if (e.key === 'Tab') {
       //  e.preventDefault();
    }
}*/
