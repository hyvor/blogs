import CodemirrorEditor, {CODEMIRROR_MODES} from "../../ReusableComponents/CodemirrorEditor";
import React, {useCallback, useEffect, useState} from "react";
import {ThemeFile} from "../../types";
import {useThemeActions, useThemeValues} from "../use";
import {CheckCircle} from "react-bootstrap-icons";


export default function TextEditor({ file, ext } : {file: ThemeFile, ext: keyof typeof CODEMIRROR_MODES}) {

    const { getOriginalFileById } = useThemeValues()
    const { updateFile, setFileContent } = useThemeActions()

    const originalFile = getOriginalFileById(file.id) as ThemeFile
    const changed = originalFile.content !== file.content;

    const [ isSaving, setIsSaving ] = useState(false)

    const handleKeyPress = useCallback((e: KeyboardEvent) => {
        if (e.key === "s" && (e.ctrlKey || e.metaKey)) {
            e.preventDefault()
        }
    }, [file])

    function handleSave(val: string) {
        setIsSaving(true);
        updateFile({
            id: file.id,
            content: val,
            onUpdate: () => setIsSaving(false)
        });
    }

    useEffect(() => {
        document.addEventListener("keydown", handleKeyPress);
        return () => document.removeEventListener("keydown", handleKeyPress);
    }, []);

    return <div className="text-editor">

        <div className="save-button-wrap">
            { !changed && !isSaving && <span className="saved"><span>Saved</span>&nbsp;<CheckCircle /></span> }
            { changed && !isSaving && <span className="button medium" onClick={() => handleSave(file.content || '')}>SAVE</span> }
            { isSaving && <span className="saving">Saving...</span> }
        </div>

        <CodemirrorEditor
            id={file.id}
            value={file.content || ''}
            onChange={val => setFileContent(file.id, val)}
            onSave={val => handleSave(val)}
            extension={ext}
        />

    </div>

}