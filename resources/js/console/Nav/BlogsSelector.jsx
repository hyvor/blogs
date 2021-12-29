import { useActions, useValues } from 'kea';
import React, { useState } from 'react'
import { useRef } from 'react';
import { useEffect } from 'react';


import {ChevronExpand} from 'react-bootstrap-icons';
import onOutsideClick from '../../helpers/onOutsideClick';
import blogsLogic from '../logic/blogsLogic';
import subdomainLogic from '../logic/subdomainLogic';

let lastActiveSubdomain = null;

export default function BlogsSelector() {

    const { blogs, findBlogBySubdomain } = useValues(blogsLogic)
    const { subdomain: activeSubdomain } = useValues(subdomainLogic)
    const { setSubdomain } = useActions(subdomainLogic)


    const [ isListOpen, setIsListOpen ] = useState(false);
    const listRef = useRef(null);

    let activeBlog = findBlogBySubdomain(activeSubdomain || lastActiveSubdomain);

    if (!activeBlog) {
        activeBlog = blogs[0];
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
        setSubdomain(subdomain)
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
                    blogs.map(blog => {
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