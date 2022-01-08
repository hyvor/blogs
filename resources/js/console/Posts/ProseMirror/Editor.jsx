import React, { useEffect, useRef, useState } from 'react';
import {EditorState} from "prosemirror-state"
import {EditorView} from "prosemirror-view"
import {Schema, DOMParser} from "prosemirror-model"
import {addListNodes} from "prosemirror-schema-list"

import HBSchema from './schema';
import plugins from './plugins';
import { Node } from 'prosemirror-model';
// import {schema as HBSchema} from 'prosemirror-schema-basic';

import {useProseMirror, ProseMirror} from 'use-prosemirror';

let view;

export default function Editor(props) {

    const editorRef = useRef(null);

    const [state, setState] = useProseMirror({
        schema: HBSchema,
        doc: props.content ? Node.fromJSON(HBSchema, props.content) : null,
        plugins: plugins(HBSchema)
    })

    useEffect(() => {
        props.onChange(state.doc.toJSON());
    }, [state]);

   /*  useEffect(() => {

        const schema = new Schema({
            nodes: addListNodes(HBSchema.spec.nodes, "paragraph block*", "block"),
            marks: HBSchema.spec.marks
        })

        view = new EditorView(editorRef.current, {
            state: EditorState.create({
                doc: Node.fromJSON(schema, )
                plugins: plugins(schema)
            })
        })
        window.view = view;

    }, []); */

    return <div className="post-editor-wrap" onClick={() => false && view && view.focus()}>
        <ProseMirror state={state} onChange={setState} />
    </div>

}