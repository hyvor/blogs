import { useActions, useValues } from 'kea';
import React, { useState } from 'react'
import { useRef } from 'react';
import { useEffect } from 'react';
import { router } from "kea-router";


import {ChevronExpand, GripVertical} from 'react-bootstrap-icons';
import numberFormatter from '../../helpers/numberFormatter';
import onOutsideClick from '../../helpers/onOutsideClick';
import userBlogsLogic from '../logic/userBlogsLogic';
import subdomainLogic from '../logic/subdomainLogic';
import { ReactSortable } from 'react-sortablejs';

let lastActiveSubdomain = null;

export default function BlogsSelector() {

    const { blogs, findBlogBySubdomain } = useValues(userBlogsLogic)
    const { setBlogs, saveBlogsSort } = useActions(userBlogsLogic)
    const { subdomain: activeSubdomain } = useValues(subdomainLogic)
    const { setSubdomain } = useActions(subdomainLogic)

    const { push } = useActions(router);

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
        setSubdomain(subdomain, null, true);
        closerRef.current(true);
    }

    function handleCreateBlog() {
        push("/console/new");
    }

    const sortableRef = useRef(null)

    const [isDragging, setIsDragging] = useState(false)

    function setDragging() {
        sortableRef.current.classList.add("dragging");
    }
    function unsetDragging() {
        sortableRef.current.classList.remove("dragging");
    }

    return <div className="blog-selector">
        <div className="value" onClick={isListOpen ? null : openList}>
            <div className="name">{ activeBlog.blog.name }</div>
            <div><ChevronExpand /></div>
        </div>
        <div 
            className={"popup-list box" + (isListOpen ? " active" : " inactive")}
            ref={listRef}
        >
            <div 
                className={"blog-list" + (isDragging ? " dragging" : "")}
            >
                <ReactSortable
                    list={blogs}
                    setList={(blogs) => setBlogs(blogs)}
                    onEnd={() => {
                        saveBlogsSort()
                        setIsDragging(false);
                    }}
                    onStart={() => {
                        setIsDragging(true);
                    }}

                    /**
                     * Otherwise it doesn't when blog preview page is opened
                     */
                    forceFallback={true}

                    animation={200}
                >
                    {
                        blogs.map(({blog, user}) => {
                            return <div 
                                key={blog.id} 
                                className={"blog" + (blog.subdomain === activeSubdomain ? " active" : "")}
                                onClick={() => handleBlogChange(blog.subdomain)}
                                onMouseDown={() => setIsDragging(true)}
                                onMouseUp={() => setIsDragging(false)}
                            >
                                <div className="blog-row-wrap">
                                    <div className="blog-row">
                                        <div className="row-left">{blog.name}</div>
                                        <div className="row-right">
                                            <span className="plan-name">{blog.plan || "Personal"}</span>
                                            <span className="global-tag-role">{user.role}</span>
                                        </div>
                                    </div>
                                    <div className="blog-row">
                                        <div className="row-left">{blog.subdomain}.hyvorblogs.io</div>
                                        <div className="row-right">
                                            { numberFormatter(blog.posts_count)} Posts &middot;&nbsp;
                                            { numberFormatter(blog.users_count) } Users</div>
                                    </div>
                                </div>
                                <div className="sort-icon-wrap">
                                    <GripVertical />
                                </div>
                            </div>
                        })
                    }
                </ReactSortable>
            </div>
            <div className="create-button-view">
                <button className="button medium secondary" onClick={handleCreateBlog}>Create a blog</button>
            </div>
        </div>
    </div>

}