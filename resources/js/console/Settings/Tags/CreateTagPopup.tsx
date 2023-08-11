import React, {useState} from 'react'
import {Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import Input from "../../ReusableComponents/Input";
import {useTagsActions, useTagsValues} from "./useTags";

export default function CreateTagPopup({ onClose } : { onClose: Function }) {

    const { createAjax } = useTagsValues()
    const { create } = useTagsActions()

    const [name, setName] = useState('');

    function handleClick() {
        create({name, onCreate: () => onClose()})
    }

    return <Popup
        header={<PopupHeaderDefault title="Create Tag" />}
        body={
            <PopupBodyDefault>
                <Input
                    title="Name"
                    type="text"
                    name="name"
                    value={name}
                    onChange={setName}
                    placeholder="Blogging"
                    autoFocus={true}
                />
            </PopupBodyDefault>
        }
        footer={
            <PopupFooterDoubleButton
                onCancel={onClose}
                onClick={handleClick}
                name='Create'
                isLoading={createAjax.status === 'loading'}
                loadingName="Creating"
            />
        }
    />;
}