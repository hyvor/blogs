import React, {useState} from 'react'
import { Check, Clock, Dot, Plus } from 'react-bootstrap-icons';
import languagesLogic from "../../logic/languagesLogic";
import {useValues} from "kea";
import getSubdomain from "../../logic-helpers/subdomain";
import {usePostActions, usePostValues} from "./helpers";
import {Language, Post, PostStatus} from "../../types";
import Spinner from "../../ReusableComponents/Spinner";

export default function PostLanguageSelector({ id }: { id: number }) {

    const { languages } = useValues(languagesLogic({subdomain: getSubdomain()}))

    return <div className="post-languages">
        {
            languages.map(lang =>
                <LanguageTag
                    key={lang.id}
                    id={id}
                    language={lang}
                />
            )
        }
    </div>

}

function LanguageTag({ id, language } : { id: number, language: Language }) {

    const { post, editorState } = usePostValues(id)
    const { changeEditorState, createVariant } = usePostActions(id)

    const [isCreating, setIsCreating] = useState(false)

    function handleChange() {
        changeEditorState('languageId', language.id);
    }

    function handleCreate() {
        setIsCreating(true)
        createVariant({
            languageId: language.id,
            onCreate: () => {
                setIsCreating(false);
                handleChange()
            }
        })
    }

    const variant = post.variants[language.id]
    let statusIcon;
    let tip;
    let onClick = handleChange;
    if (!variant) {
        statusIcon = <Plus />
        tip = `Add ${language.name} post`;
        onClick = handleCreate
    } else if (variant.status === 'published') {
        statusIcon = <Check />
        tip = `${language.name} - Published`;
    } else if (variant.status === 'scheduled') {
        statusIcon = <Clock size={9} />
        tip = `${language.name} - Scheduled`;
    } else if (variant.status === 'draft') {
        statusIcon = <Dot />
        tip = `${language.name} - Draft`;
    }

    return <span
        key={language.id}
        className={"lang-tag" + (editorState.languageId === language.id ? " active" : "")}
        data-tip={tip}
        onClick={onClick}
    >
        <span className="code">{language.code}</span>
        <span className="status-icon">
            { isCreating ? <Spinner size={7} dark={true} /> : statusIcon }
        </span>
    </span>

}

export function getLangTagIconByPostStatus(status: PostStatus) {
    if (status === 'published') {
        return <Check />
    } else if (status === 'scheduled') {
        return <Clock size={9} />
    } else if (status === 'draft') {
        return <Dot size={7} />
    }
}