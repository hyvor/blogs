import React, {useState} from 'react'
import { useValues } from 'kea'
import { Plus } from 'react-bootstrap-icons'
import languagesLogic from "../logic/languagesLogic"
import {Language} from "../types"
import Spinner from "./Spinner";
import getSubdomain from "../logic-helpers/subdomain";

type onChangeType = (languageId: number) => void;
type variantCreatorType = (props: {languageId: number, onCreate: Function}) => void;

interface LanguageSelectorProps {
    languageId: number,
    variantsLanguageIds: Array<number>,
    onChange: onChangeType,
    variantCreator: variantCreatorType
}

export default function LanguageSelector(
    { languageId, variantsLanguageIds, onChange, variantCreator } : LanguageSelectorProps
) {

    const { languages } = useValues(languagesLogic({subdomain: getSubdomain()}))

    return <div>
        <div className="global-languages-list">
            {
                languages.map(lang =>
                    <LangTag
                        key={lang.id}
                        lang={lang}
                        isActive={languageId === lang.id}
                        onChange={onChange}
                        variantCreator={variantCreator}
                        variantsLanguageIds={variantsLanguageIds}
                    />
                )
            }
        </div>
    </div>

}

interface LangTagProps {
    lang: Language,
    isActive: boolean,
    onChange: onChangeType,
    variantCreator: variantCreatorType,
    variantsLanguageIds: number[],
}

function LangTag({lang, isActive, variantsLanguageIds, onChange, variantCreator} : LangTagProps) {

    const [isCreating, setIsCreating] = useState<boolean>(false);

    function handleClick() {
        if (variantsLanguageIds.indexOf(lang.id) < 0) {
            setIsCreating(true)
            variantCreator({
                languageId: lang.id,
                onCreate: () => {
                    setIsCreating(false)
                    onChange(lang.id)
                }})
        } else {
            onChange(lang.id)
        }
    }

    return <span
        key={lang.id}
        className={"lang-tag" + (isActive ? " active" : "")}
        onClick={() => handleClick()}
    >
        <span className="code">{lang.code}</span>
        <span className="status-icon">
            {
                isCreating ? <Spinner size={7} dark={true} /> :
                    (variantsLanguageIds.indexOf(lang.id) >= 0 ? null : <Plus />)
            }
        </span>
    </span>

}