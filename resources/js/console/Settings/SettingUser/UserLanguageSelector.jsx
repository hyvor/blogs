import React from 'react'
import { useActions, useValues } from 'kea';
import { Check, Clock, Dot, Plus } from 'react-bootstrap-icons';
import languagesLogic from '../../logic/languagesLogic';
import usersLogic from '../../logic/usersLogic';



export default function UserLanguageSelector({id, subdomain, languages, variant, currentLanguageId, onChange }) 
{
    const usersLogicBuilt = usersLogic({subdomain})
    const { createVariant} = useActions(usersLogicBuilt)

    createVariant({
        userId: id,
        languageId: currentLanguageId 
    });

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

            // <span className="lang-tag">
            //     <span className="code">en</span>
            //     <span className="status-icon">
            //         <Plus />
            //     </span>
            // </span>
            ))
        }
        </div> 
    </div>
}