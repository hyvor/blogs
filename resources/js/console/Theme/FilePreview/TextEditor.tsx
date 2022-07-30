import CodemirrorEditor, {CODEMIRROR_MODES} from "../../ReusableComponents/CodemirrorEditor";
import React, {useEffect} from "react";
import {ThemeFile} from "../../types";
import {useThemeActions} from "../use";


export default function TextEditor({ file, ext } : {file: ThemeFile, ext: keyof typeof CODEMIRROR_MODES}) {

    const { setFileContent, editorSaveFile } = useThemeActions()

    function handleKeyPress(e: KeyboardEvent) {
        if (e.key === "s" && (e.ctrlKey || e.metaKey)) {
            editorSaveFile(file.id);
        }
    }

    useEffect(() => {
        document.addEventListener("keydown", handleKeyPress);
        return () => document.removeEventListener("keydown", handleKeyPress);
    }, []);

    return <div className="text-editor">

        <CodemirrorEditor
            value={file.content || ''}
            onChange={(val: string) => setFileContent(file.id, val)}
            mode={ext}
        />

    </div>

}