import React, { useEffect, useState, useRef } from 'react';
import Post from './Post';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import postsLogic from '../logic/postsLogic';
import Loader from '../ReusableComponents/Loader';
import PostsFilters from './PostsFilters';
import NoResults from '../ReusableComponents/NoResults';
import PostsListRow from './PostsListRow';
import NoPost from "./NoPost";
import languagesLogic from "../logic/languagesLogic";

export default function Posts( { postId } : { postId: number | null } ) {

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

    const { loadAjax: languageLoadAjax } = useValues(languagesLogic({subdomain}))

    function handleScroll(e: React.UIEvent<HTMLDivElement>) {
        var el = e.currentTarget;
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

    return languageLoadAjax.status === 'loading' ?
        <div className="posts-not-ready box">
            <Loader size={40} />
        </div>
        : <div className="posts-view">
            <div id="posts-selector" className="box box-left">
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
                            <div className="posts-loading"><Loader/></div> :
                            <div className="posts-loaded-wrap">
                                {
                                    postsList.length ?
                                        postsList.map((id: number) => <PostsListRow
                                            key={id}
                                            id={id}
                                            subdomain={subdomain}
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
            <div id="post-viewer" className="box box-right">
                {
                    postId ?
                    <Post subdomain={subdomain} id={postId} /> :
                    <NoPost />
                }
            </div>
        </div>
}