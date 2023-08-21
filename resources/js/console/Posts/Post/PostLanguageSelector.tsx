import React, {useState} from 'react'
import {Check, Clock, Dot, Magic, Plus} from 'react-bootstrap-icons';
import languagesLogic from "../../logic/languagesLogic";
import {useValues} from "kea";
import getSubdomain from "../../logic-helpers/subdomain";
import {usePostActions, usePostValues} from "./helpers";
import {Language, Post, PostStatus} from "../../types";
import Spinner from "../../ReusableComponents/Spinner";
import AutoTranslate from "./AutoTranslate";
import Tooltip from '../../ReusableComponents/Tooltip';

export default function PostLanguageSelector({ id }: { id: number }) {

    const { languages } = useValues(languagesLogic({subdomain: getSubdomain()}))
    const { editorState } = usePostValues(id)

    const currentLanguage = languages.find(l => l.id === editorState.languageId)
    const [isAutoTranslating, setIsAutoTranslating] = useState(false);

    return <div className="post-languages-wrap">
        <div className="post-languages">
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

        {
            !currentLanguage?.is_primary &&
            <div>
                <button className="button light small" onClick={() => setIsAutoTranslating(true)}>
                    Auto-Translate <Magic />
                </button>
                { isAutoTranslating && <AutoTranslate id={id} onCancel={() => setIsAutoTranslating(false)} /> }
            </div>
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

    const variant = post.variants.find(v => v.language_id === language.id);
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

    return <Tooltip
        tooltip={tip}
        position="bottom"
    >
        <span
            key={language.id}
            className={"lang-tag" + (editorState.languageId === language.id ? " active" : "")}
            data-tip={tip}
            onClick={onClick}
            data-testid={"lang-tag-" + language.code}
        >
            <span className="code">{language.code}</span>
            <span className="status-icon">
                { isCreating ? <Spinner size={7} dark={true} /> : statusIcon }
            </span>
        </span>
    </Tooltip>

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