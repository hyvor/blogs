import React from 'react'

export default function PostLanguageSelector({ languages }) {

    return <div className="post-languages">
        {languages.map(l => {
            return <span 
                key={l.id}
                className={"lang-tag" + (l.code === 'en' ? " active" : "")}
            >{l.code}</span>
        })}
    </div>

}