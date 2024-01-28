import React, {Fragment, useEffect, useState} from 'react';
import Loader from '../../ReusableComponents/Loader';
import NoResults from '../../ReusableComponents/NoResults';
import {useTagsActions, useTagsValues} from "./useTags";
import {Plus} from "react-bootstrap-icons";
import {Table, TableHead, TableHeadItem} from "../../ReusableComponents/Table";
import Tag from "./Tag";
import TableLoadMore from "../../ReusableComponents/TableLoadMore";
import CreateTagPopup from "./CreateTagPopup";

export default function Tags() {

    const { tagsList, tagsListHasMore, tags, loadAjax, loadMoreAjax } = useTagsValues()
    const { load, loadMore } = useTagsActions()

    useEffect(load, []);

    const [isCreating, setIsCreating] = useState(false);

    return <div className="settings-tags">

        <div className="title">
            Tags <button
            className="button small inactive"
            onClick={() => setIsCreating(true)}
        >Create <Plus/></button>
        </div>

        <div>
            { 
                loadAjax.status === 'loading' ?
                    <Loader padding={40}/> 
                :
                (
                    tagsList.length ?

                        <Table>
                            <TableHead>
                                <TableHeadItem>Name</TableHeadItem>
                                <TableHeadItem>Description</TableHeadItem>
                                <TableHeadItem>Posts</TableHeadItem>
                                <div />
                            </TableHead>
                            <Fragment>
                                {
                                    tagsList.map(tagId => <Tag key={tagId} tag={tags[tagId]} />)
                                }
                            </Fragment>
                            <TableLoadMore
                                hasMore={tagsListHasMore}
                                isLoading={loadMoreAjax.status === 'loading'}
                                onClick={loadMore}
                            />
                        </Table>
                    :
                    <NoResults
                        text="No tags found"
                    />
                )
            }
        </div>

        {
            isCreating &&
            <CreateTagPopup onClose={() => setIsCreating(false)} />
        }

    </div> 
}