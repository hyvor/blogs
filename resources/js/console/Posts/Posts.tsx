import React, { useEffect, useState, useRef } from 'react';
import Post from './Post/Post';
import { useActions, useValues } from 'kea';
import postsLogic from '../logic/postsLogic';
import Loader from '../ReusableComponents/Loader';
import PostsFilters from './PostsFilters';
import NoResults from '../ReusableComponents/NoResults';
import PostsListRow from './PostsListRow';
import { useSubdomain } from "../logic-helpers/subdomain";
import { TableHead, TableHeadItem, Table } from '../ReusableComponents/Table';
import languagesLogic from '../logic/languagesLogic';
import ActionButton from '../ReusableComponents/ActionButton';

export default function Posts({ postId }: { postId: number | undefined }) {

    const subdomain = useSubdomain()

    const { languages, getLanguageById } = useValues(languagesLogic({ subdomain }));

    const [newPostClick, setNewPostClick] = useState(false);

    const postLogicSubdomain = postsLogic({ subdomain })
    const {
        postsList, loadPostsListAjax, postsListHasMore, loadPostsListMoreAjax,
        filters
    } = useValues(postLogicSubdomain);
    const {
        loadPostsListMore, createPost,
        changeFilter,
        setActivePostId
    } = useActions(postLogicSubdomain)

    function handleScroll(e: React.UIEvent<HTMLDivElement>) {
        const el = e.currentTarget;

        if (
            loadPostsListMoreAjax.status !== 'loading' &&
            postsListHasMore &&
            el.scrollTop + el.clientHeight >= el.scrollHeight - 30
        ) {
            loadPostsListMore({ offset: postsList.length })
        }
    }

    const onLoad = () => {
        setNewPostClick(false);
    }

    function handleNew() {
        setNewPostClick(true);
        createPost({onLoad: onLoad});
    }

    useEffect(() => {
        setActivePostId(postId ? postId : null);
    }, [postId]);


    /*
    * If a postId is defined, display the Post view in fullscreen mode or the Posts List view 
    */
    return <div className="posts-view" data-testid="posts">
        {
            postId && loadPostsListAjax.status !== 'loading' ? <Post id={postId} subdomain={subdomain} type="post" /> :
                <div id="posts-selector" className="box box-left box-content">
                    <div className="middle-heading">
                        <div>
                            Posts
                        </div>
                        <ActionButton 
                            className="medium new-post-button"
                            status={newPostClick ? 'loading' : 'stale'}
                            staleName="+ New"
                            loadingName="Creating" 
                            successName="Created"
                            errorName="Try again"
                            staleOnClick={handleNew}
                        /> 
                    </div>
                    <PostsFilters filters={filters} changeFilter={changeFilter} />
                    <div className="posts-list" onScroll={handleScroll}>
                        {
                            loadPostsListAjax.status === 'loading' ?
                                <div className="posts-loading"><Loader padding={100} /></div> :
                                <div className="posts-loaded-wrap post-headers">
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