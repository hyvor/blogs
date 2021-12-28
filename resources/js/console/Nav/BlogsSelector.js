import React, { useState } from 'react'
import { useRef } from 'react';
import { useEffect } from 'react';


import {ChevronExpand} from 'react-bootstrap-icons';
import { useParams } from 'react-router-dom';
import onOutsideClick from '../../helpers/onOutsideClick';
import useActiveSubdomain from '../state/useActiveSubdomain';
import useBlogsState from '../state/useBlogsState';

let lastActiveSubdomain = null;

export default function BlogsSelector() {

    const blogsState = useBlogsState();
    const activeSubdomainState = useActiveSubdomain();
    const activeSubdomain = activeSubdomainState.get();

    const [ isListOpen, setIsListOpen ] = useState(false);
    const listRef = useRef(null);

    let activeBlog = blogsState.getBlogBySubdomain(activeSubdomain || lastActiveSubdomain);

    if (!activeBlog) {
        activeBlog = blogsState.get()[0];
    }

    useEffect(() => {
        lastActiveSubdomain = activeSubdomain;
    }, [activeSubdomain]);

    let closerRef = useRef(null);
    function openList() {
        setIsListOpen(true)

        closerRef.current = onOutsideClick(listRef.current, closeList, true);
    }
    function closeList() {
        setIsListOpen(false)
    }

    function handleBlogChange(subdomain) {
        activeSubdomainState.change(subdomain)
        closerRef.current();
    }

    return <div className="blog-selector">
        <div className="value" onClick={isListOpen ? null : openList}>
            <div className="name">{ activeBlog.name }</div>
            <div><ChevronExpand /></div>
        </div>
        <div 
            className={"popup-list box" + (isListOpen ? " active" : " inactive")}
            ref={listRef}
        >
            <div className="blog-list">
                {
                    blogsState.get().map(blog => {
                        return <div 
                            key={blog.id} 
                            className={"blog" + (blog.subdomain === activeSubdomain ? " active" : "")}
                            onClick={() => handleBlogChange(blog.subdomain)}
                        >
                            <div className="blog-row">
                                <div className="row-left">{blog.name}</div>
                                <div className="row-right">
                                    <span className="global-tag-role">{blog.role}</span>
                                </div>
                            </div>
                            <div className="blog-row">
                                <div className="row-left">{blog.subdomain}.hyvorblogs.io</div>
                                <div className="row-right">{blog.posts_count} Posts</div>
                            </div>
                        </div>
                    })
                }
            </div>
            <div className="create-button-view">
                <button className="button medium secondary">Create a blog</button>
            </div>
        </div>
    </div>

}