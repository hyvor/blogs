import React from 'react'
import { Check, Clock, Dot, Plus } from 'react-bootstrap-icons';
import Tooltip from '../ReusableComponents/Tooltip';

export default function PostLanguageSelector({ languages, variants, currentLanguageId, onChange }) {

    return <div className="post-languages">
        {
            languages.map(lang => {
                const variant = variants.find(variant => variant.language_id === lang.id)
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
        }

        <Tooltip place="bottom" />

    </div>

}