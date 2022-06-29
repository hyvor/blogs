import React, { useEffect } from "react";

import { useActions, useValues } from "kea";
import subdomainLogic from "../logic/subdomainLogic";
import themeLogic from "../logic/themeLogic";
import CodemirrorEditor, { CODEMIRROR_MODES } from "../ReusableComponents/CodemirrorEditor";

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

    const ext = activeFile.name.split('.').pop();

    return <div className="editor-file">

        <CodemirrorEditor 
            value={activeFile.content}
            onChange={val => setFileContent(activeFile.id, val)}
            mode={ CODEMIRROR_MODES[ext] }
        />

    </div>

}