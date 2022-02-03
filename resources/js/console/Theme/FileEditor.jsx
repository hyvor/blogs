import React, { useEffect } from "react";
import CodeMirror from '@uiw/react-codemirror';
import 'codemirror/addon/display/autorefresh';
import 'codemirror/addon/comment/comment';
import 'codemirror/addon/edit/matchbrackets';
import 'codemirror/keymap/sublime';
import 'codemirror/theme/solarized.css';

import 'codemirror/mode/javascript/javascript';
import 'codemirror/mode/twig/twig';
import 'codemirror/mode/htmlmixed/htmlmixed';
import 'codemirror/mode/css/css';


import { useActions, useValues } from "kea";
import subdomainLogic from "../logic/subdomainLogic";
import themeLogic from "../logic/themeLogic";

export default function FileEditor() {

    const { subdomain } = useValues(subdomainLogic);
    const themeLogicInst = themeLogic({subdomain})
    const { editorActiveFileId, getFileById } = useValues(themeLogicInst);
    const { editorSaveFile, setFileContent } = useActions(themeLogicInst);

    const activeFile = getFileById(editorActiveFileId);

    function handleKeyPress(e) {
        if (e.key === "s" && (e.ctrlKey || e.metaKey)) {
            editorSaveFile(id);
        }
    }

    useEffect(() => {
        document.addEventListener("keydown", handleKeyPress);
        return () => document.removeEventListener("keydown", handleKeyPress);
    }, []);

    let mode = null;
    const ext = activeFile.name.split('.').pop();
    const modes = {
        scss: 'text/x-scss',
        twig: { name: 'twig', base: 'text/html' },
        js: 'js'
    };
    mode = modes[ext] || null;

    return <div className="editor-file">

        <CodeMirror
            value={activeFile.content}
            lazyLoadMode={false}
            options={{
                theme: 'solarized',
                keyMap: 'sublime',
                mode,
                lineWrapping: true
            }}
            onChange={inst => setFileContent(activeFile.id, inst.doc.getValue())}
        />

    </div>

}