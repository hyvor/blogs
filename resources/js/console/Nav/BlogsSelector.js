import React from 'react'
import { useEffect } from 'react';


import {ChevronExpand} from 'react-bootstrap-icons';
import { useParams } from 'react-router-dom';
import useActiveSubdomain from '../state/useActiveSubdomain';
import useBlogsState from '../state/useBlogsState';

let lastActiveSubdomain = null;

export default function BlogsSelector() {

    const blogsState = useBlogsState();
    const subdomain = useActiveSubdomain().get();

    let activeBlog = blogsState.getBlogBySubdomain(subdomain || lastActiveSubdomain);

    if (!activeBlog) {
        activeBlog = blogsState.get()[0];
    }

    useEffect(() => {
        lastActiveSubdomain = subdomain;
    }, [subdomain]);

    return <div className="blog-selector">
        <div className="name">{ activeBlog.name }</div>
        <div><ChevronExpand /></div>
    </div>

}