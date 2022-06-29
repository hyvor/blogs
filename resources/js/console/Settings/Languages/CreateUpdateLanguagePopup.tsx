import React, {useState} from "react";
import { Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import {Language} from "../../types";
import Input from "../../ReusableComponents/Input";
import languagesLogic from "../../logic/languagesLogic";
import {useActions, useValues} from "kea";
import getSubdomain from "../../logic-helpers/subdomain";

interface Props {
    language?: Language,
    onCancel: Function
}

export default function CreateUpdateLanguagePopup({ language, onCancel }: Props) {

    const isCreate = !language

    const languagesLogicInst = languagesLogic({subdomain: getSubdomain()})
    const { create, update } = useActions(languagesLogicInst)
    const { createAjax, updateAjax } = useValues(languagesLogicInst)

    const [ name, setName ] = useState(language ? language.name : "");
    const [ code, setCode ] = useState(language ? language.code : "");

    function handleClick() {
        if (isCreate) {
            create({name, code, onCreate: onCancel});
        } else {
            update({id: language.id, name, code, onUpdate: onCancel});
        }
    }

    return <Popup
        header={<PopupHeaderDefault title={isCreate ? "Add Language" : "Update Language"} />}
        body={<PopupBodyDefault>
            <div className="lang-add-popup-body">
                <Input
                    title="Name"
                    type="text"
                    name="name"
                    autoComplete="off"
                    value={name}
                    onChange={setName}
                    maxLength={50}
                    autoFocus={true}
                    placeholder="English"
                />
                <Input
                    title="Code"
                    type="text"
                    name="language-code"
                    autoComplete="off"
                    value={code}
                    onChange={setCode}
                    maxLength={12}
                    placeholder="en"
                />
            </div>
        </PopupBodyDefault>}
        footer={
            <PopupFooterDoubleButton
                onCancel={onCancel}
                onClick={handleClick}
                name={ isCreate ? "Add" : "Update" }
                loadingName={ isCreate ? "Adding" : "Updating"}
                isLoading={ createAjax.status === 'loading' || updateAjax.status === 'loading' }
            />
        }
    />

}