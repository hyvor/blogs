import React from 'react';
import Post from './Post/Post';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import Loader from '../ReusableComponents/Loader';
import NoResults from '../ReusableComponents/NoResults';
import pagesLogic from '../logic/pagesLogic';
import PostsListRow from './PostsListRow';
import NoPost from './NoPost';
import getSubdomain from "../logic-helpers/subdomain";
import { Table, TableHead } from '../ReusableComponents/Table';
import ActionButton from '../ReusableComponents/ActionButton';

export default function Pages({ postId }: { postId: number | undefined }) {

    const subdomain = getSubdomain();
    const pagesLogicInst = pagesLogic({ subdomain })
    const { pagesList, loadPagesListAjax } = useValues(pagesLogicInst);
    const { createPage } = useActions(pagesLogicInst)

    const [newPageClick, setNewPageClick] = React.useState(false);

    function handleNew() {
        setNewPageClick(true);
        createPage(setNewPageClick);
    }

    return <div className="posts-view">
        {
            postId && loadPagesListAjax.status === 'success' ?
                <Post id={postId} subdomain={subdomain} type="page" />
                :
                <div className="box box-left box-content">
                    <div className="middle-heading">
                        <div>
                            Pages
                        </div>
                        <ActionButton 
                            className="medium new-post-button"
                            status={newPageClick ? 'loading' : 'stale'}
                            staleName="+ New"
                            loadingName="Creating" 
                            successName="Created"
                            errorName="Try again"
                            staleOnClick={handleNew}
                        /> 
                    </div>
                    <div className="posts-list box-content">
                        {
                            loadPagesListAjax.status === 'loading' ?
                                <div className="posts-loading"><Loader padding={100} /></div> :

                                <div className="posts-loaded-wrap">
                                    <div className='page-headers'></div>

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
                </div>}

    </div>
}