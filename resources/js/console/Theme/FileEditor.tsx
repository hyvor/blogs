import React, { useEffect } from "react";
import CodemirrorEditor, { CODEMIRROR_MODES } from "../ReusableComponents/CodemirrorEditor";
import {useThemeActions, useThemeValues} from "./use";

export default function FileEditor() {

    const { editorOpenedFileId, getFileById } = useThemeValues();
    const { editorSaveFile, setFileContent } = useThemeActions();

    const activeFile = getFileById(editorOpenedFileId);

    function handleKeyPress(e) {
        if (e.key === "s" && (e.ctrlKey || e.metaKey)) {
            editorSaveFile(id);
        }
    }

    useEffect(() => {
        document.addEventListener("keydown", handleKeyPress);
        return () => document.removeEventListener("keydown", handleKeyPress);
    }, []);

    if (!activeFile)
        return null;

    const ext = activeFile.name.split('.').pop();

    return <div className="file-editor">

        <div className="file-name">{ activeFile.name }</div>

        <div className="file-preview">

            <CodemirrorEditor
                value={activeFile.content || ''}
                onChange={(val: string) => setFileContent(activeFile.id, val)}
                mode={ CODEMIRROR_MODES[ext] }
            />

        </div>



    </div>

}