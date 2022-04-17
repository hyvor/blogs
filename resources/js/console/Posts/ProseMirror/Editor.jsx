import React, { useState } from 'react';
import {EditorState, NodeSelection} from "prosemirror-state"

import HBSchema from './schema';
import plugins from './plugins';

/**
 * 
 */
import {ProseMirror} from 'use-prosemirror';
import useUpdateEffect from '../../../helpers/hooks/useUpdateEffect';
import RichView from './nodeview-rich';
import Figcaption from './nodeview-figcaption';
import Heading from './nodeview-heading';
import Callout from './Callout/nodeview-callout';
import CodeBlock from './nodeview-codeblock';
import Image from './Image/nodeview-image';
import Bookmark from './nodeview-bookmark';


function getState(val) {
    val = val ? JSON.parse(val) : null
    const newState = {
        schema: HBSchema,
        plugins: plugins(HBSchema),
    }
    if (val) {
        newState.doc = HBSchema.nodeFromJSON( val );
    }
    return () => EditorState.create(newState);
}

const nodeViews = {
    rich(...args) {
        return new RichView(...args);
    },
    figcaption(...args) {
        return new Figcaption(...args);
    },
    heading(...args) {
        return new Heading(...args);
    },
    callout(...args) {
        return new Callout(...args)
    },
    code_block(...args) {
        return new CodeBlock(...args)
    },
    image(...args) {
        return new Image(...args)
    },
    bookmark(...args) {
        return new Bookmark(...args)
    }
}

export default function Editor(props) {

    const [state, setState] = useState(getState(props.value));

    useUpdateEffect(() => {
        setState(getState(props.value))
    }, [props.id, props.currentLanguageId]);

    function handleChange(state) {
        props.onChange(JSON.stringify(state.doc.toJSON()));
        setState(state);
    }


    return <ProseMirror 
        state={state}
        nodeViews={nodeViews}
        onChange={handleChange}
        handleClickOn={handleClickOn}
        handleKeyDown={handleKeyDown}
        editable={() => props.editable}
    />
}

function handleClickOn(view, pos, node, posBefore, e) {
    // if (pos != posBefore) return false
    //return false;

    /**
     * Select figure when clicking on it
     */
    if (node.type.name === "figure" && e.target.nodeName !== "INPUT") {
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
function handleKeyDown(view, e) {
    if (e.key === 'Tab') {
        e.preventDefault();
    }
}
