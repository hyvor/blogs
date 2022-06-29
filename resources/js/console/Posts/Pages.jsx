import React from 'react';
import Post from './Post/Post';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import Loader from '../ReusableComponents/Loader';
import NoResults from '../ReusableComponents/NoResults';
import pagesLogic from '../logic/pagesLogic';
import PostsListRow from './PostsListRow';
import NoPost from './NoPost';

export default function Pages( { postId } ) {

    const { subdomain } = useValues(subdomainLogic)
    const pagesLogicInst = pagesLogic({subdomain})
    const { pagesList, loadPagesListAjax } = useValues(pagesLogicInst);
    const { createPage } = useActions(pagesLogicInst)

    function handleNew() {
        createPage();
    }

    return <div className="posts-view">
        <div className="box box-left">
            <div className="middle-heading">
                <div>
                    Pages
                </div>
                <button 
                    className="button small"
                    onClick={handleNew}
                >+ New</button>
            </div>
            <div className="posts-list">
                {
                    loadPagesListAjax.status === 'loading' ?
                    <div className="posts-loading"><Loader /></div> :

                    <div className="posts-loaded-wrap">
                        {
                            pagesList.length ?
                            pagesList.map(id => <PostsListRow key={id} id={id} subdomain={subdomain} />) :
                            <NoResults 
                                text="No pages found"
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