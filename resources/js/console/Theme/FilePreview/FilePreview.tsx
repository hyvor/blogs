import React, { useState }  from "react";
import { CODEMIRROR_MODES } from "../../ReusableComponents/CodemirrorEditor";
import { useThemeActions, useThemeValues} from "../use";
import TextEditor from "./TextEditor";
import AssetImagePreview from "./AssetImagePreview";
import NoPreview from "./NoPreview";
import FileTopBar from "./FileTopBar";
import ConfigDef from "../../../configdef/ConfigDef";
import FileSaver from "./FileSaver";

export default function FilePreview() {

    const { editorOpenedFileId, getFileById, getFileByFolderAndName } = useThemeValues();
    const { setFileContent } = useThemeActions();

    const activeFile = getFileById(editorOpenedFileId);

    const [configYaml, setConfigYaml] = useState(false);

    if (!activeFile)
        return null;

    const ext = activeFile.name.split('.').pop() || '';

    const textExtensions = ['scss', 'twig', 'js', 'yaml'];
    const imageExtensions = ['png', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'gif', 'apng', 'avif', 'svg', 'webp'];
    let body;

    if (textExtensions.indexOf(ext) >= 0) {

        if (activeFile.folder === null && activeFile.name === 'config.yaml' && !configYaml) {

            body = <div className="config-def-wrap">
                <ConfigDef 
                    configYaml={activeFile.content || ''}
                    configDefYaml={getFileByFolderAndName(null, 'config.def.yaml')?.content || ''}
                    onConfigChange={(val) => setFileContent(activeFile.id, val)}
                />
                <FileSaver file={activeFile} />
            </div>

        } else {
            body = <TextEditor file={activeFile} ext={ext as keyof typeof CODEMIRROR_MODES} />
        }

    } else if (activeFile.folder === 'assets' && imageExtensions.indexOf(ext) >= 0) {
        body = <AssetImagePreview file={activeFile}  />
    } else {
        body = <NoPreview file={activeFile} />
    }


    return <div className="file-preview">
        <FileTopBar 
            file={activeFile}
            configYaml={configYaml}
            setConfigYaml={setConfigYaml}
        />
        { body }
    </div>

}