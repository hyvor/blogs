import React from 'react';
import BlogsSelector from './BlogsSelector';

import {
    NavLink
} from "react-router-dom";

export default function Nav() {
    return <div id="left">
        <div id="left-header" className="box">
            <img src="/img/logo.png" id="left-header-image-1" className="round-image-40"></img>
            <div id="left-header-image-2" className="round-image-40"></div>
        </div>
        <div id="left-nav" className="box">
            <BlogsSelector />

            <NavLink to="/" exact>Blog</NavLink>

            <div className="left-divider"></div>

            <NavLink to="/posts">Posts</NavLink>
            <NavLink to="/pages">Pages</NavLink>

            <div className="left-divider"></div>

            <a>Theme</a>
            <a>Users</a>
            <a>Billing</a>
            <a>Settings</a>
        </div>
    </div>
}