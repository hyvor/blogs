import React from 'react'
import { useActions, useValues } from 'kea';
import { Check, Clock, Dot, Plus } from 'react-bootstrap-icons';
import subdomainLogic from '../../logic/subdomainLogic';
import languagesLogic from '../../logic/languagesLogic';
import navigationLogic from '../../logic/navigationLogic';



export default function TagLanguageSelector({id, subdomain, languages, variants, currentLanguageId, onChange }) 
{
    const navigationLogicBuilt = navigationLogic({subdomain})
    const { createVariant} = useActions(navigationLogicBuilt)

    createVariant({
        navigationId: id,
        languageId: currentLanguageId
    });

    return <div>
        <div className="global-languages-list">
        {
            languages.map(lang => {
                const variant = variants[lang.id]
                let statusIcon;
                if (!variant) {
                    statusIcon = <Plus />
                } else {
                    statusIcon = <Check />
                }

                return <span 
                    key={lang.id}
                    className={"lang-tag" + (currentLanguageId === lang.id ? " active" : "")}
                    // data-tip={tip}
                    onClick={() => onChange(lang.id)}
                >
                    <span className="code">{lang.code}</span>
                    <span className="status-icon">
                        { statusIcon }
                    </span>
                </span>
            })
        }

       
        </div> 
    </div>
}