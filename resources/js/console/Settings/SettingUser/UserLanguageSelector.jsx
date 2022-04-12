import React, {useState, useEffect, memo, useMemo, useCallback} from 'react'
import { useActions, useValues } from 'kea';
import { Check, Clock, Dot, Plus } from 'react-bootstrap-icons';
import languagesLogic from '../../logic/languagesLogic';
import usersLogic from '../../logic/usersLogic';



export default function UserLanguageSelector({id, subdomain, languages, variants, currentLanguageId, onChange }) 
{
    const usersLogicBuilt = usersLogic({subdomain})
    const { createVariant} = useActions(usersLogicBuilt)

    createVariant({
        userId: id,
        languageId: currentLanguageId 
    });
    
    return <div> 
        <div className="global-languages-list">
        {/* {
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
        } */}

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


            {/* <span className="lang-tag">
                <span className="code">en</span>
                <span className="status-icon">
                    <Plus />
                </span>
            </span> */}
        </div> 
    </div>
}