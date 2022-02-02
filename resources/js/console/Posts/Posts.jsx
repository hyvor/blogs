import React, { useEffect, useState, useRef } from 'react';
import Post from './Post';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import postsLogic from '../logic/postsLogic';
import Loader from '../ReusableComponents/Loader';
import NavLink from '../ReusableComponents/NavLink';
import postLogic from '../logic/postLogic';
import PostFilters from './PostFilters';

export default function Posts( { postId } ) {

    const { subdomain } = useValues(subdomainLogic)
    const postLogicSubdomain = postsLogic({subdomain})
    const { 
        postsList, loadPostsListAjax, postsListHasMore, loadPostsListMoreAjax,
        filters
    } = useValues(postLogicSubdomain);
    const { 
        loadPostsListMore, createPost,
        changeFilter
    } = useActions(postLogicSubdomain)

    function handleScroll(e) {
        var el = e.target;
        if (
            loadPostsListMoreAjax.status !== 'loading' &&  
            postsListHasMore &&
            el.scrollTop + el.clientHeight >= el.scrollHeight
        ) {
            loadPostsListMore({ offset: postsList.length })
        }
    }

    function handleNew() {
        createPost();
    }

    return <div className="posts-view">
        <div className="box box-left">
            <div className="middle-heading">
                <div>
                    Posts
                </div>
                <button 
                    className="button small"
                    onClick={handleNew}
                >+ New</button>
            </div>
            <PostFilters filters={filters} changeFilter={changeFilter} />
            <div className="posts-list" onScroll={handleScroll}>
                {
                    loadPostsListAjax.status === 'loading' ?
                    <div className="posts-loading"><Loader /></div> :

                    <div className="posts-loaded-wrap">
                        {
                            postsList.map(id => <PostRow key={id} id={id} subdomain={subdomain} />)
                        }
                    </div>
                }
            </div>
        </div>
        <div className="box box-right">
            {
                postId ?
                <Post subdomain={subdomain} id={postId} /> : 
                <NoPost />
            }
        </div>
    </div>
}

function PostRow({id, subdomain}) {

    const { post } = useValues(postLogic({id}))
    const postsLink = `/console/${subdomain}/posts`
    const toLink = `${postsLink}/${post.id}`

    return <NavLink
        key={post.id} 
        href={location.pathname === toLink ? postsLink : toLink }
        className={"posts-list-item" + (false ? " active" : "") + ` ${post.status}` }>
        <div className="post-title">{
                    post.status !== 'published' ? 
                    <span className={`post-status ${post.status}`}>{post.status}</span>
                    : null}{ post.title || '(Untitled)' }</div>
        
        <div className="post-data">
            <div className="post-date">
                { new Date(post.created_at).toDateString() }
            </div>
            <div className="post-author">by Ishini Avindya</div>
        </div>

        <div className="post-tags">
            <span className="post-tag">#creative</span>
        </div>
    </NavLink>

}

function NoPost() {

    return <div>
        Posts are the heart of the blog!
    </div>

}