import {ThemeFile} from "../../types";
import {PencilFill, Trash} from "react-bootstrap-icons";
import React, {useState} from "react";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import {useThemeActions} from "../use";
import {toast} from "react-toastify";
import FileNameInput from "../FileNameInput";
import Switch from "../../ReusableComponents/Switch";

interface Props {
    file: ThemeFile,
    configYaml: boolean,
    setConfigYaml: (configYaml: boolean) => void
}

export default function FileTopBar({file, configYaml, setConfigYaml} : Props) {

    const { updateFile, deleteFile, editorCloseFile } = useThemeActions()

    const [isUpdating, setIsUpdating] = useState(false)
    const [isDeleting, setIsDeleting] = useState(false)

    function handleDelete() {
        setIsDeleting(false)
        editorCloseFile()
        deleteFile({id: file.id})
        toast.success('File deleted');
    }

    function handleUpdate(name: string) {
        setIsUpdating(false)
        updateFile({id: file.id, name})
    }

    return <div className="file-top-bar">

        {
            isUpdating ?
                <FileNameInput
                    name={file.name}
                    onCreate={name => handleUpdate(name)}
                    onCancel={() => setIsUpdating(false)}
                />
                : file.name
        }

        {
            !isUpdating &&
            <span className="buttons">
                <button className="icon-button" onClick={() => setIsUpdating(true)}><PencilFill size={10}/></button>
                <button className="icon-button" onClick={() => setIsDeleting(true)}><Trash size={10}/></button>
            </span>
        }

        {
            isDeleting &&
            <PopupConfirm
                title="Delete File"
                text="Please confirm to delete this file"
                name="Delete"
                buttonClass="danger"
                onClick={handleDelete}
                onCancel={() => setIsDeleting(false)}
            />
        }

        {
            file.folder === null && file.name === 'config.yaml' &&
            <div className="config-yaml-switch">
                <span className="switch-title">YAML</span>
                <Switch
                    checked={configYaml}
                    onChange={checked => setConfigYaml(checked)}
                />
            </div> 
        }

    </div>

}