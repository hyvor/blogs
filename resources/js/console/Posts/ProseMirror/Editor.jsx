import React, { useEffect, useRef, useState } from 'react';
import {EditorState} from "prosemirror-state"
import {EditorView} from "prosemirror-view"
import {Schema, DOMParser} from "prosemirror-model"
import {addListNodes} from "prosemirror-schema-list"

import HBSchema from './schema';
import plugins from './plugins';
import { Node } from 'prosemirror-model';
// import {schema as HBSchema} from 'prosemirror-schema-basic';

/**
 * 
 */
import {ProseMirror} from 'use-prosemirror';
import useUpdateEffect from '../../../helpers/hooks/useUpdateEffect';


function getState(val) {
    val = val ? JSON.parse(val) : null
    const newState = {
        schema: HBSchema,
        plugins: plugins(HBSchema)
    }
    if (val) {
        newState.doc = HBSchema.nodeFromJSON( val );
    }
    return () => EditorState.create(newState);
}

export default function Editor(props) {

    const [state, setState] = useState(getState(props.value));

    useUpdateEffect(() => {
        setState(getState(props.value))
    }, [props.id]);

    function handleChange(state) {
        props.onChange(JSON.stringify(state.doc.toJSON()));
        setState(state);
    }

    return <ProseMirror state={state} onChange={handleChange} />

}