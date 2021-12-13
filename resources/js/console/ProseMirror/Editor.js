import React, { useEffect, useRef, useState } from 'react';
import {EditorState} from "prosemirror-state"
import {EditorView} from "prosemirror-view"
import {Schema, DOMParser} from "prosemirror-model"
import {addListNodes} from "prosemirror-schema-list"
import {exampleSetup} from "prosemirror-example-setup"

import HBSchema from './schema';
// import {schema as HBSchema} from 'prosemirror-schema-basic';


export default function Editor() {

    const editorRef = useRef(null);

    useEffect(() => {

        const mySchema = new Schema({
            nodes: addListNodes(HBSchema.spec.nodes, "paragraph block*", "block"),
            marks: HBSchema.spec.marks
        })

        window.view = new EditorView(editorRef.current, {
            state: EditorState.create({
              doc: DOMParser.fromSchema(mySchema).parse("<p>Hello World</p>"),
              plugins: exampleSetup({schema: mySchema, menuBar: false})
            })
        })

    });

    return <div className="post-editor-wrap">
        <div ref={editorRef} />
    </div>

}