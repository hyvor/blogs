import React, { useEffect, useState, useRef } from 'react';
import Post from './Post/Post';
import { useActions, useValues } from 'kea';
import postsLogic from '../logic/postsLogic';
import Loader from '../ReusableComponents/Loader';
import PostsFilters from './PostsFilters';
import NoResults from '../ReusableComponents/NoResults';
import PostsListRow from './PostsListRow';
import { useSubdomain } from "../logic-helpers/subdomain";

export default function Posts({ postId }: { postId: number | undefined }) {

    const subdomain = useSubdomain()
    const postLogicSubdomain = postsLogic({ subdomain })
    const {
        postsList, loadPostsListAjax, postsListHasMore, loadPostsListMoreAjax,
        filters
    } = useValues(postLogicSubdomain);
    const {
        loadPostsListMore, createPost,
        changeFilter
    } = useActions(postLogicSubdomain)

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

    /*
    * If a postId is defined, display the Post view in fullscreen mode or the Posts List view 
    */
    return <div className="posts-view">
        {
            postId && loadPostsListAjax.status !== 'loading' ? <Post id={postId} subdomain={subdomain} type="post" /> :
                <div id="posts-selector" className="box box-left box-content">
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
                                    <div className='post-list-header'>
                                        <p>Post</p>
                                        <p>Authors</p>
                                        <p>Tags</p>
                                        <p>Status</p>
                                    </div>
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
                </div>}

    </div>
}