import React, { useEffect } from "react";
// import CodeMirror from '@uiw/react-codemirror';

import {Controlled as CodeMirror} from 'react-codemirror2'

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
        js: 'text/javascript'
    };
    mode = modes[ext] || null;

    return <div className="editor-file">

        <CodeMirror
            value={activeFile.content}
            options={{
                theme: 'solarized',
                keyMap: 'sublime',
                tabSize: 4,
                mode,
                lineWrapping: true
            }}
            onBeforeChange={(_, __, value) => setFileContent(activeFile.id, value)}
        />

    </div>

}