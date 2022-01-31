import React from 'react';
import BlogsSelector from './BlogsSelector';

import { useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import NavLink from '../ReusableComponents/NavLink';
import blogsLogic from '../logic/blogsLogic';
import { Exclamation } from 'react-bootstrap-icons';

export default function Nav() {

    const { subdomain } = useValues(subdomainLogic);
    const { findBlogBySubdomain } = useValues(blogsLogic);
    const { blog, blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    return <div id="left">
        <div id="left-header" className="box">
            <img src="/img/logo.png" id="left-header-image-1" className="round-image-40"></img>
            <div id="left-header-image-2" className="round-image-40"></div>
        </div>
        <div id="left-nav" className="box">
            <BlogsSelector />

            <NavLink href={`/console/${subdomain}`} exact={1}>Blog</NavLink>

            <div className="left-divider"></div>

            <NavLink href={`/console/${subdomain}/posts`}>Posts</NavLink>
            <NavLink href={`/console/${subdomain}/pages`}>Pages</NavLink>

            <div className="left-divider"></div>

            <NavLink href={`/console/${subdomain}/theme`}>Theme</NavLink>
            <NavLink href={`/console/${subdomain}/billing`}>
                <span className="name">Billing</span>
                <span className="mark">
                    {
                        blog.is_on_trial ?
                        <span className="trial-days-left">4 days left</span> : null
                    }
                    {
                        currentSubscription && 
                        (currentSubscription.status === 'past_due' || currentSubscription.status === 'paused')
                        ?
                        <span className="subscription-issue-icon">
                            <Exclamation />
                        </span>
                        : null
                    }
                </span>
            </NavLink>
            <NavLink href={`/console/${subdomain}/settings`}>Settings</NavLink>

        </div>
    </div>
}