import React from 'react';
import {KeaAjaxObject} from "kea-ajax";
import Loader from "./Loader";

interface LoadMoreProps {
    hasMore: boolean,
    isLoading: boolean,
    onClick: Function
}

export default function TableLoadMore({ hasMore, isLoading, onClick } : LoadMoreProps) {

    if (!hasMore) {
        return null;
    } else if (isLoading) {
        return <div className="global-table-load-more"><Loader size="small" /></div>
    } else {
        return <div className="global-table-load-more"><button
            className="button inactive medium"
            onClick={() => onClick()}
        >Load More</button></div>
    }

}