import React from 'react';
import NavLink from '../ReusableComponents/NavLink';
import postLogic from '../logic/postLogic';
import { useValues } from 'kea';

export default function PostListRow({ id, subdomain }) {

    const { post } = useValues(postLogic({id}))
    const postsLink = `/console/${subdomain}/` + (post.is_page ? 'pages' : 'posts')
    const toLink = `${postsLink}/${post.id}`

    return <NavLink
        key={post.id} 
        href={location.pathname === toLink ? postsLink : toLink }
        className={"posts-list-item" + (false ? " active" : "") + ` ${post.status}` }>

        <div className="post-title">{ post.title || '(Untitled)' }</div>
        
        <div className="post-data">
            <div className="post-date">
                { new Date(post.created_at).toDateString() }
            </div>
            {
                !post.is_page ?
                <div className="post-author">by Ishini Avindya</div> :
                null
            }
        </div>

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