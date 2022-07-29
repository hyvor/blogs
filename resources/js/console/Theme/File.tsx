import React from 'react'
import {useThemeActions, useThemeValues} from "./use";

export default function File({ id, name } : {id: number, name: string}) {

    const { editorOpenedFileId } = useThemeValues()
    const { editorOpenFile } = useThemeActions()

    return <div
        className={"file" + (editorOpenedFileId === id ? " active" : "")}
        onClick={() => editorOpenFile(id)}
    >{name}</div>

}