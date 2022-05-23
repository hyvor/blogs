import React from 'react'
import { useActions, useValues } from 'kea';
import { Check, Clock, Dot, Plus } from 'react-bootstrap-icons';
import languagesLogic from "../logic/languagesLogic";
import subdomainLogic from "../logic/subdomainLogic";

interface LanguageSelectorProps {
    languageId: number,
    hasVariant: boolean,
    onChange: (languageId: number) => void
}

export default function LanguageSelector({ languageId, hasVariant, onChange } : LanguageSelectorProps)
{

    const { languages } = useValues(languagesLogic({subdomain: subdomainLogic.values.subdomain}))

    return <div>
        <div className="global-languages-list">
            {
                languages.map(lang => (
                    <span
                        key={lang.id}
                        className={"lang-tag" + (languageId === lang.id ? " active" : "")}
                        onClick={() => onChange(lang.id)}
                    >
                <span className="code">{lang.code}</span>
                <span className="status-icon">
                    {
                        hasVariant ? null : <Plus />
                    }
                </span>
            </span>
                ))
            }
        </div>
    </div>
}