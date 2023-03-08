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

export default function Pages({ postId }: { postId: number | undefined }) {

    const subdomain = getSubdomain();
    const pagesLogicInst = pagesLogic({ subdomain })
    const { pagesList, loadPagesListAjax } = useValues(pagesLogicInst);
    const { createPage } = useActions(pagesLogicInst)

    function handleNew() {
        createPage();
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
                        <button
                            className="button small new-post-button"
                            onClick={handleNew}
                        >+ New</button>
                    </div>
                    <div className="posts-list box-content">
                        {
                            loadPagesListAjax.status === 'loading' ?
                                <div className="posts-loading"><Loader /></div> :

                                <div className="posts-loaded-wrap post-headers">
                                    <Table>
                                        <TableHead>
                                            <div className='page-list-header'>
                                                <div>Page</div>
                                                <div>Status</div>
                                            </div>
                                        </TableHead>
                                    </Table>
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