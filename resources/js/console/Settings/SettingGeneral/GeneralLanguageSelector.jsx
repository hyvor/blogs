import React from 'react'
import { useActions, useValues } from 'kea';
import { Check, Clock, Dot, Plus } from 'react-bootstrap-icons';
import blogLogic from '../../logic/blogLogic';


export default function GeneralLanguageSelector({subdomain, languages, currentLanguageId, onChange }) 
{
    const blogLogicBuilt = blogLogic({subdomain})
    const { createVariant} = useActions(blogLogicBuilt)

    /*createVariant({
        languageId: currentLanguageId 
    });*/

    return <div>
        <div className="global-languages-list">
        {
            languages.map(lang => (
            <span 
                key={lang.id}
                className={"lang-tag" + (currentLanguageId === lang.id ? " active" : "")}
                onClick={() => onChange(lang.id)}
            >
                <span className="code">{lang.code}</span>
                <span className="status-icon">
                    <Plus />
                </span>
            </span>
            ))
        }
        {/* {
            languages.map(lang => {
                const variant = variant.language_id
                let statusIcon;
                let tip;
                if (!variant) {
                    statusIcon = <Plus />
                    tip = `Add ${lang.name} post`;
                } else if (variant.status === 'published') {
                    statusIcon = <Check />
                    tip = `${lang.name} - Published`;
                } else if (variant.status === 'scheduled') {
                    statusIcon = <Clock size={9} />
                    tip = `${lang.name} - Scheduled`;
                } else if (variant.status === 'draft') {
                    statusIcon = <Dot />
                    tip = `${lang.name} - Draft`;
                }

                return <span 
                    key={lang.id}
                    className={"lang-tag" + (currentLanguageId === lang.id ? " active" : "")}
                    data-tip={tip}
                    onClick={() => onChange(lang.id)}
                >
                    <span className="code">{lang.code}</span>
                    <span className="status-icon">
                        { statusIcon }
                    </span>
                </span>
            })
        } */}
        </div> 
    </div>
}