import React, { useEffect, useState, useRef } from 'react';
import Select from '../ReusableComponents/Select';
import { components } from 'react-select';
import Post from './Post';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import postsLogic from '../logic/postsLogic';
import Loader from '../ReusableComponents/Loader';
import NavLink from '../ReusableComponents/NavLink';

export default function Posts( { postId } ) {

    const { subdomain } = useValues(subdomainLogic)
    const postLogicSubdomain = postsLogic({subdomain})
    const { 
        posts,
        postsList, postsListLoadStatus, postsListHasMore, postsListLoadMoreStatus, 
        filters 
    } = useValues(postLogicSubdomain);
    const { getPostsLoadMore, createPost } = useActions(postLogicSubdomain)

    const { changeFilter } = useActions(postLogicSubdomain)

    function handleScroll(e) {
        var el = e.target;
        if (
            postsListLoadMoreStatus !== 'loading' &&  
            postsListHasMore &&
            el.scrollTop + el.clientHeight >= el.scrollHeight
        ) {
            getPostsLoadMore({ offset: postsList.length })
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
            <PostFilterView filters={filters} changeFilter={changeFilter} />
            <div className="posts-list" onScroll={handleScroll}>
                {
                    postsListLoadStatus === 'loading' ?
                    <div className="posts-loading"><Loader /></div> :

                    <div className="posts-loaded-wrap">
                        {
                            postsList.map(pId => {
                                const post = posts[pId];
                                const postsLink = `/console/${subdomain}/posts`
                                const toLink = `${postsLink}/${post.id}`

                                return <NavLink
                                    key={post.id} 
                                    href={location.pathname === toLink ? postsLink : toLink }
                                    className={"posts-list-item" + (false ? " active" : "") + ` ${post.status}` }>
                                    <div className="post-title">{
                                                post.status !== 'published' ? 
                                                <span className={`post-status ${post.status}`}>{post.status}</span>
                                                : null}{ post.title || '(Untitled)' }</div>
                                    
                                    <div className="post-data">
                                        <div className="post-date">
                                            { new Date(post.created_at).toDateString() }
                                        </div>
                                        <div className="post-author">by Ishini Avindya</div>
                                    </div>


                                    <div className="post-tags">
                                        <span className="post-tag">#creative</span>
                                    </div>
                                </NavLink>
                            })
                        }
                    </div>
                }
            </div>
        </div>
        <div className="box box-right">
            {
                postId ?
                <Post subdomain={subdomain} id={postId} /> : <div>No post ID</div>
            }
        </div>
    </div>
}

function PostFilterView({ filters, changeFilter }) {

    const statusOptions = [
        { value: 'all', label: 'All' },
        { value: 'published', label: 'Published (43)' },
        { value: 'draft', label: 'Draft (50)' },
        { value: 'deleted', label: 'Deleted (2)' }
    ]
    const authorsOptions = [
        { value: 'all', label: 'All (500)' },
        { value: 'you', label: 'You (45)' },
        { value: 'others', label: 'Others (241)' }
    ];
    const tagsOptions = [
        { value: 'all', label: 'All' },
        { value: 'creative', label: '#creative'}
    ]
    const dateOptions = [
        { value: 'all', label: 'All' },
        { value: 'today', label: 'Today'}
    ];

    function handleChange(name, v) {
        changeFilter(name, v.value);
    }
    function updateSearch(e) {
        if (filters.search !== e.target.value)
            changeFilter('search', e.target.value)
    }

    // search is only updated when blur or enterClick
    const [search, setSearch] = useState(filters.search);


    return <div className="posts-filtering">
        <div className="post-filters">
            <PostsFilter name="status" value={filters.status} options={statusOptions} onChange={handleChange} />
            <PostsFilter name="author" value={filters.author} options={authorsOptions} onChange={handleChange} />
            <PostsFilter name="tag" value={filters.tag} options={tagsOptions} onChange={handleChange} />
            <PostsFilter name="date" value={filters.date} options={dateOptions} onChange={handleChange} />
        </div>
        <div className="post-search">
            <input 
                className="input" 
                value={search} 
                onChange={(e) => setSearch(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && updateSearch(e)}
                onBlur={updateSearch}
                placeholder="Search..."
            ></input>
        </div>
    </div>

}

function PostsFilter( { name, value, options, onChange } ) {

    const SingleValue = p => (
        <components.SingleValue {...p}>
          {p.data.label.replace(/ \(.+\)/, '')}
        </components.SingleValue>
    );

    value = options.find(i => i.value === value) || options[0];

    return <div className="posts-filter">
        <div className="posts-filter-name">{name}</div>
        <Select 
            value={value} 
            type="small" 
            options={options}
            onChange={(v) => onChange(name, v)}

            

            // https://stackoverflow.com/a/52484756/9059939
            // to remove number
            components={{ SingleValue }}
        />
    </div>   
}