import React from 'react';
import BlogsSelector from './BlogsSelector';

import { useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import NavLink from '../ReusableComponents/NavLink';
import blogsLogic from '../logic/blogsLogic';
import { Exclamation } from 'react-bootstrap-icons';
import dayjs from 'dayjs';

export default function Nav() {

    const { subdomain } = useValues(subdomainLogic);
    if (!subdomain) {
        return null;
    }

    const { findBlogBySubdomain } = useValues(blogsLogic);
    const { blog, blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const trialDaysDiff = blog.is_on_trial ?  dayjs.unix(blog.trial_ends_at).diff(dayjs(), 'd') : 0;
    
    return <div id="left">
        <div id="left-header" className="box">
            <a href="/">
                <img src="/img/logo.png" id="left-header-image-1" className="round-image-40"></img>
            </a>
            <div className="left-header-pp">
                <a href={`https://${appConfig.domains.hyvor}/account`} target="_blank">
                    <img src="https://i.pravatar.cc/60?img=3" id="left-header-image-1" className="round-image-40"></img>
                </a>
            </div>
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
                        <span className="trial-days-left">{trialDaysDiff} days left</span> : null
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