import { useActions, useValues } from 'kea';
import React, { forwardRef, useState } from 'react';
import subdomainLogic from '../logic/subdomainLogic';
import themeLogic from '../logic/themeLogic';
import FileEditor from './FileEditor';
import { ReactSortable } from "react-sortablejs";
import { useEffect } from 'react';

const SortableWrap = forwardRef(({children}, ref) => <div className="sort-wrap" ref={ref}>{children}</div>);

export default function FileBrowser() {

    const { subdomain } = useValues(subdomainLogic);
    const themeLogicInst = themeLogic({subdomain})
    const { editorOpenedFilesIds, editorActiveFileId, getFileById, hasFileUpdated } = useValues(themeLogicInst);
    const { editorCloseFile, editorSetActiveFileId, editorSetOpenedFiles } = useActions(themeLogicInst)

    const [ fileList, setFileList ] = useState([])

    useEffect(() => {
        setFileList(editorOpenedFilesIds.map(id => ({id})));
    }, [editorOpenedFilesIds])

    function handleSetFileList(list) {
        editorSetOpenedFiles(list.map(l => l.id));
    }

    function handleClose(e, id) {
        e.stopPropagation();
        editorCloseFile(id);
    }

    return <div className="editor-view">

        <div className="editor-nav">
            <ReactSortable tag={SortableWrap} list={fileList} setList={handleSetFileList}>
                {
                    editorOpenedFilesIds.map(id => {
                        const file = getFileById(id)
                        const hasUpdated = hasFileUpdated(id);

                        return <div 
                            key={file.id} 
                            className={"file-slice" + (editorActiveFileId === id ? " active" : "")}
                            onClick={() => editorSetActiveFileId(id)}
                        >
                            <span className="name">{ file.name }</span>
                            <span className={"close" + (hasUpdated ? " updated" : "") } onClick={e => handleClose(e, id)}>
                                {!hasUpdated ? <span>&times;</span> : <span>&#9679;</span>}
                            </span>
                        </div>
                    })
                }
            </ReactSortable>
        </div>

        {
            editorActiveFileId ?
            <FileEditor /> :
            null
        }

    </div>;

}