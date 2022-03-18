import React, { useEffect, useState, useRef } from 'react';
import Post from './Post';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import postsLogic from '../logic/postsLogic';
import Loader from '../ReusableComponents/Loader';
import postLogic from '../logic/postLogic';
import PostsFilters from './PostsFilters';
import NoResults from '../ReusableComponents/NoResults';
import PostsListRow from './PostsListRow';
import NoPost from './NoPost';

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
            <PostsFilters filters={filters} changeFilter={changeFilter} />
            <div className="posts-list" onScroll={handleScroll}>
                {
                    loadPostsListAjax.status === 'loading' ?
                    <div className="posts-loading"><Loader /></div> :

                    <div className="posts-loaded-wrap">
                        {
                            postsList.length ?
                            postsList.map(id => <PostsListRow 
                                    key={id} 
                                    id={id} 
                                    subdomain={subdomain} 
                                    filters={filters} 
                            />) :
                            <NoResults 
                                text="No posts found"
                                padding={60}
                                imageWidth={150}
                            />
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