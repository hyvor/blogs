import React, { useEffect, useState } from "react";
import { ThemeFile } from "../../types";
import { useThemeActions, useThemeValues } from "../use";
import { CheckCircle } from "react-bootstrap-icons";


export default function FileSaver({file, isSaving} : {file: ThemeFile, isSaving?: boolean}) {

    const { getOriginalFileById } = useThemeValues()
    const { updateFile, setFileContent } = useThemeActions()

    const originalFile = getOriginalFileById(file.id) as ThemeFile

    const changed = originalFile.content !== file.content;
    const [ isSavingState, setIsSavingState ] = useState(isSaving || false);

    useEffect(() => {
        setIsSavingState(isSaving || false);
    }, [isSaving]); 

    function handleSave(val: string) {
        setIsSavingState(true);
        updateFile({
            id: file.id,
            content: val,
            onUpdate: () => setIsSavingState(false)
        });
    }

    return <div className="save-button-wrap">
        { !changed && !isSavingState && <span className="saved"><span>Saved</span>&nbsp;<CheckCircle /></span> }
        { changed && !isSavingState && <span className="button medium" onClick={() => handleSave(file.content || '')}>SAVE</span> }
        { isSavingState && <span className="saving">Saving...</span> }
    </div>

}