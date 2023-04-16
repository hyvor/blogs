import {useValues} from "kea";
import languagesLogic from "../../logic/languagesLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {usePostValues} from "./helpers";
import {Popup, PopupConfirm, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import React from "react";
import DualSetting from "../../ReusableComponents/DualSetting";


export default function AutoTranslate({id, onCancel}: { id: number, onCancel: Function}) {

    const { languages } = useValues(languagesLogic({subdomain: getSubdomain()}))
    const { editorState } = usePostValues(id)

    const currentLanguage = languages.find(l => l.id === editorState.languageId)

    return <PopupConfirm
        title="Auto-Translate"
        text={
            <div>
                <DualSetting
                    title="Base Language"
                    right="English"
                />
            </div>
        }
        name="Auto-Translate"
        onClick={() => {

        }}
        onCancel={onCancel}
    />

}