import React from 'react';
import NavLink from '../ReusableComponents/NavLink';
import postLogic from '../logic/postLogic';
import { useValues } from 'kea';
import dayjs from 'dayjs';
import languagesLogic from '../logic/languagesLogic';
import LangTag from '../ReusableComponents/LangTag';
import { getLangTagIconByPostStatus } from './Post/PostLanguageSelector';

export default function PostsListRow({ id , subdomain } : {id: number, subdomain: string}) {

    const { languages, getLanguageById } = useValues(languagesLogic({subdomain}))
    const { post } = useValues(postLogic({id}))

    const postsLink = `/console/${subdomain}/` + (post.is_page ? 'pages' : 'posts')
    const toLink = `${postsLink}/${post.id}`

    const languageId = languages[0].id

    const variant = post.variants[languageId];

    const authorsNames = post.authors.map(author => author.variants[0].name).join(", ");

    return <NavLink
        key={post.id} 
        href={location.pathname === toLink ? postsLink : toLink }
        className={"posts-list-item" + ` ${variant.status}` }>

        <div className="post-title">{ variant.title || '(Untitled)' }</div>
        
        <div className="post-data">
            <div className="post-date">
                { dayjs.unix(post.published_at || post.created_at).format('MMM D, YYYY') }
            </div>
            {
                !post.is_page ?
                <div className="post-author">
                    by {authorsNames}
                </div> :
                null
            }
        </div>


        {
            languages.length > 1 ?
            <div className="post-languages">
                {
                    Object.entries(post.variants).map(([, variant]) => {
                        const lang = getLanguageById(variant.language_id)

                        return lang ?
                            <LangTag
                                key={lang.id}
                                code={lang.code}
                                icon={getLangTagIconByPostStatus(variant.status)}  
                            /> : null
                    })
                }
            </div> : null  }

        <div className="post-tags-wrap">

            <div className="post-tags">
            {
                !post.is_page ?
                    post.tags.map(tag => {
                        return <span key={tag.id} className="post-tag">{tag.variants[languageId].name}</span>
                    })
                : null
            }
            </div>
            <div className="post-status-wrap">
                <span className={`global-post-status ${variant.status}`}>{variant.status}</span>
            </div>
        </div>

        
    </NavLink>

}