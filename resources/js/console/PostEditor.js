import React, { useEffect } from 'react';
import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header'; 
import List from '@editorjs/list'; 
import Embed from '@editorjs/embed';

export default function PostEditor() {

    useEffect(() => {
        const editor = new EditorJS({
            holder: 'post-editor',
            tools: {
                header: Header,
                list: List,
                embed: Embed
            }
        });

        window.addEventListener("keydown", function(e) {
            if (e.ctrlKey && e.key === "d") {
                e.preventDefault();
                editor.save().then((outputData) => {
                    console.log('Article data: ', outputData)
                });
            }
        })

    });

    return <div id="post-editor">

    </div>

}   