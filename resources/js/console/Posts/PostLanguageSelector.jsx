import React from 'react'
import { Check, Clock, Dot, Pencil, Plus } from 'react-bootstrap-icons';

export default function PostLanguageSelector({ languages, variants, currentLanguageId, onChange, onCreate }) {

    return <div className="post-languages">
        {
            languages.map(lang => {
                const variant = variants[lang.id]
                let statusIcon;
                let tip;
                let onClick = () => onChange(lang.id);
                if (!variant) {
                    statusIcon = <Plus />
                    tip = `Add ${lang.name} post`;
                    onClick = () => onCreate(lang.id)
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
                    onClick={onClick}
                >
                    <span className="code">{lang.code}</span>
                    <span className="status-icon">
                        { statusIcon }
                    </span>
                </span>
            })
        }

    </div>

}

export function getLangTagIconByPostStatus(status) {
    let icon;
    if (status === 'published') {
        icon = <Check />
    } else if (status === 'scheduled') {
        icon = <Clock size={9} />
    } else if (status === 'draft') {
        icon = <Pencil size={7} />
    }
    return icon;
}