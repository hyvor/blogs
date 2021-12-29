import React, { useEffect, useState } from 'react';
import Select from '../ReusableComponents/Select';
import { components } from 'react-select';

import PostEditor from './PostEditor';
import { useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import postsLogic from '../logic/postsLogic';
import Loader from '../ReusableComponents/Loader';


export default function Posts() {

    const { subdomain } = useValues(subdomainLogic)
    const { posts, postsLoading } = useValues(postsLogic({subdomain}));

    const statusOptions = [
        { value: 'all', label: 'All' },
        { value: 'published', label: 'Published (43)' },
        { value: 'draft', label: 'Draft (50)' },
        { value: 'deleted', label: 'Deleted (2)' }
    ]
    const ownershipOptions = [
        { value: 'all', label: 'All' },
        { value: 'you', label: 'You (45)' },
    ];
    const tagsOptions = [
        { value: 'all', label: 'All' },
        { value: 'creative', label: '#creative'}
    ]
    const dateOptions = [
        { value: 'all', label: 'All' },
        { value: 'today', label: 'Today'}
    ];

    return <div className="posts-view">
        <div className="box box-left">
            <div className="middle-heading">
                <div>
                    Posts
                </div>
                <button className="button small">+ New</button>
            </div>
            <div className="posts-filtering">
                <div className="post-filters">
                    <PostsFilter name="Status" defaultValue={statusOptions[0]} options={statusOptions} />
                    <PostsFilter name="Author" defaultValue={ownershipOptions[0]} options={ownershipOptions} />
                    <PostsFilter name="Tags" defaultValue={tagsOptions[0]} options={tagsOptions} />
                    <PostsFilter name="Date" defaultValue={dateOptions[0]} options={dateOptions} />
                </div>
                <div className="post-search">
                    <input className="input" placeholder="Search..."></input>
                </div>
            </div>
            <div className="posts-list">
                {
                    postsLoading ?
                    <div className="posts-loading"><Loader /></div> :
                    posts.map(post => {
                        return <div key={post.id} className={"posts-list-item" + (false ? " active" : "") + ` ${post.status}` }>
                            <div className="post-title">{ 
                                        post.status !== 'published' ? 
                                        <span className={`post-status ${post.status}`}>{post.status}</span>
                                        : null}{ post.title }</div>
                            
                            <div className="post-data">
                                <div className="post-date">
                                    { new Date(post.created_at).toDateString() }
                                </div>
                                <div className="post-author">by Ishini Avindya</div>
                            </div>


                            <div className="post-tags">
                                <span className="post-tag">#creative</span>
                            </div>
                        </div>
                    })
                }
            </div>
        </div>
        <div className="box box-right">
            <PostEditor />
        </div>
    </div>
}

function PostsFilter(props) {

    const SingleValue = props => (
        <components.SingleValue {...props}>
          {props.data.label.replace(/ \(.+\)/, '')}
        </components.SingleValue>
    );

    return <div className="posts-filter">
        <div className="posts-filter-name">{props.name}</div>
        <Select 
            defaultValue={props.defaultValue} 
            type="small" 
            options={props.options}

            // https://stackoverflow.com/a/52484756/9059939
            // to remove number
            components={{ SingleValue }}
        />
    </div>   
}