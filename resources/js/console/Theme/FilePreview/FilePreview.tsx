import React, { useEffect } from "react";
import CodemirrorEditor, { CODEMIRROR_MODES } from "../../ReusableComponents/CodemirrorEditor";
import {useThemeActions, useThemeValues} from "../use";
import TextEditor from "./TextEditor";
import AssetImagePreview from "./AssetImagePreview";
import NoPreview from "./NoPreview";
import {PencilFill, Trash} from "react-bootstrap-icons";
import FileTopBar from "./FileTopBar";

export default function FilePreview() {

    const { editorOpenedFileId, getFileById } = useThemeValues();

    const activeFile = getFileById(editorOpenedFileId);

    if (!activeFile)
        return null;

    const ext = activeFile.name.split('.').pop() || '';

    const textExtensions = ['scss', 'twig', 'js', 'yaml'];
    const imageExtensions = ['png', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'gif', 'apng', 'avif', 'svg', 'webp'];
    let body;

    if (textExtensions.indexOf(ext) >= 0) {
        body = <TextEditor file={activeFile} ext={ext as keyof typeof CODEMIRROR_MODES} />
    } else if (activeFile.folder === 'assets' && imageExtensions.indexOf(ext) >= 0) {
        body = <AssetImagePreview file={activeFile}  />
    } else {
        body = <NoPreview file={activeFile} />
    }


    return <div className="file-preview">
        <FileTopBar file={activeFile} />
        { body }
    </div>

}