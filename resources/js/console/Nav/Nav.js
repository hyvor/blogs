import React from 'react';
import BlogsSelector from './BlogsSelector';

import {
    NavLink, useParams
} from "react-router-dom";
import useActiveSubdomain from '../state/useActiveSubdomain';

export default function Nav() {

    const subdomain = useActiveSubdomain().get();

    return <div id="left">
        <div id="left-header" className="box">
            <img src="/img/logo.png" id="left-header-image-1" className="round-image-40"></img>
            <div id="left-header-image-2" className="round-image-40"></div>
        </div>
        <div id="left-nav" className="box">
            <BlogsSelector />

            <NavLink to={subdomain} end>Blog</NavLink>

            <div className="left-divider"></div>

            <NavLink to={`/${subdomain}/posts`}>Posts</NavLink>
            <NavLink to={`/${subdomain}/pages`}>Pages</NavLink>

            <div className="left-divider"></div>

            <NavLink to={`/${subdomain}/theme`}>Theme</NavLink>
            <NavLink to={`/${subdomain}/settings`}>Settings</NavLink>

        </div>
    </div>
}