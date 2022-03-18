import React from 'react';
import NavLink from '../ReusableComponents/NavLink';
import postLogic from '../logic/postLogic';
import { useValues } from 'kea';
import dayjs from 'dayjs';
import languagesLogic from '../logic/languagesLogic';

export default function PostsListRow({ id, subdomain }) {

    const { languages, getLanguageById } = useValues(languagesLogic({subdomain}))

    const { post } = useValues(postLogic({id}))
    const postsLink = `/console/${subdomain}/` + (post.is_page ? 'pages' : 'posts')
    const toLink = `${postsLink}/${post.id}`

    const variant = post.variants[0];

    return <NavLink
        key={post.id} 
        href={location.pathname === toLink ? postsLink : toLink }
        className={"posts-list-item" + (false ? " active" : "") + ` ${post.status}` }>

        <div className="post-title">{ variant.title || '(Untitled)' }</div>
        
        <div className="post-data">
            <div className="post-date">
                { dayjs.unix(post.published_at || post.created_at).format('MMM D, YYYY') }
            </div>
            {
                !post.is_page ?
                <div className="post-author">by Ishini Avindya</div> :
                null
            }
        </div>


        {
            languages.length > 1 ?
            <div className="post-languages">
                {
                    post.variants.map(variant => {
                        const lang = getLanguageById(variant.language_id)

                        return lang ? <span key={lang.id} className="post-lang-tag">{ lang.code }</span> : null
                    })
                }
            </div> : null  }

        <div className="post-tags-wrap">

            <div className="post-tags">
            {
                !post.is_page ?
                <span className="post-tag">#creative</span>
                : null
            }
            </div>
            <div className="post-status-wrap">
                <span className={`post-status ${post.status}`}>{post.status}</span>
            </div>
        </div>

        
    </NavLink>

}