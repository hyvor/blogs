import {useValues} from "kea";
import languagesLogic from "../../logic/languagesLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {usePostActions, usePostValues} from "./helpers";
import {Popup, PopupConfirm, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import React from "react";
import DualSetting from "../../ReusableComponents/DualSetting";
import api from "../../lib/api";

export default function AutoTranslate({id, onCancel}: { id: number, onCancel: Function}) {

    const subdomain = getSubdomain();
    const { languages } = useValues(languagesLogic({subdomain}))
    const { editorState, post } = usePostValues(id)
    const { updateCurrentPostVariantValue, changeEditorState } = usePostActions(id);

    const currentLanguage = languages.find(l => l.id === editorState.languageId)

    function handleTranslate() {

        api.post<{title: string, content: string}>(subdomain, `/ai/translate`, {
            title: post.variants[0].title,
            content: post.variants[0].content,
            source_lang: 'EN',
            target_lang: 'FR'
        }).then(data => {

            updateCurrentPostVariantValue('title', data.title);
            updateCurrentPostVariantValue('content', data.content);
            updateCurrentPostVariantValue('content_unsaved', data.content);

            changeEditorState('version', editorState.version + 1);

        })

    }

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
            handleTranslate();
        }}
        onCancel={onCancel}
    />

}