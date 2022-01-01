import React, { useEffect, useRef, useState } from 'react';
import {EditorState} from "prosemirror-state"
import {EditorView} from "prosemirror-view"
import {Schema, DOMParser} from "prosemirror-model"
import {addListNodes} from "prosemirror-schema-list"

import HBSchema from './schema';
import plugins from './plugins';
// import {schema as HBSchema} from 'prosemirror-schema-basic';

let view;

export default function Editor() {

    const editorRef = useRef(null);

    useEffect(() => {

        const schema = new Schema({
            nodes: addListNodes(HBSchema.spec.nodes, "paragraph block*", "block"),
            marks: HBSchema.spec.marks
        })

        view = new EditorView(editorRef.current, {
            state: EditorState.create({
              doc: DOMParser.fromSchema(schema).parse(''),
              plugins: plugins(schema)
            })
        })
        window.view = view;

    }, []);

    return <div className="post-editor-wrap" onClick={() => false && view && view.focus()}>
        <div ref={editorRef} />
    </div>

}