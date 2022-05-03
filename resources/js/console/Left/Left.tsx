import React from 'react';
import BlogsSelector from './BlogsSelector';

import { useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import NavLink from '../ReusableComponents/NavLink';
import blogsLogic from '../logic/blogsLogic';
import {
    Brush,
    Chat, Coin,
    CurrencyDollar,
    Droplet,
    Exclamation,
    Files, Gear,
    House,
    Palette,
    Pencil,
    Wallet
} from 'react-bootstrap-icons';
import dayjs from 'dayjs';
import {UserBlog} from "../objects/userblog";
import {appConfig} from "../helpers";

export default function Left() {

    const { subdomain } = useValues(subdomainLogic);
    if (!subdomain) {
        return null;
    }

    const { findBlogBySubdomain } = useValues(blogsLogic);
    const { blog, blog: { subscription: currentSubscription } } : UserBlog  = findBlogBySubdomain(subdomain);

    const trialDaysDiff = blog.is_on_trial ?  dayjs.unix(blog.trial_ends_at).diff(dayjs(), 'd') : 0;
    
    return <div id="left">
        <div id="left-header" className="box">
            <NavLink href={"/console"} className="console-link" exact={1}>
                <img
                    src="/img/logo-social.png"
                    className="round-image-40"
                    alt="Hyvor Talk Logo"
                />
            </NavLink>
             <div className="left-header-pp">
                <a
                    href={`https://${appConfig().domains.hyvor}/account`}
                    target="_blank">
                    <img
                        src={appConfig().hyvorUser.picture}
                        className="round-image-40"
                        alt="Profile Picture"
                    />
                </a>
            </div>
        </div>


        <div id="left-nav" className="box">
            <BlogsSelector />

            <NavLink href={`/console/${subdomain}`} exact={1}><House /><span className="name">Blog</span></NavLink>

            <div className="left-divider"/>

            <NavLink href={`/console/${subdomain}/posts`}><Pencil /><span className="name">Posts</span></NavLink>
            <NavLink href={`/console/${subdomain}/pages`}><Files /> <span className="name">Pages</span></NavLink>
            <NavLink href={`/console/${subdomain}/comments`}><Chat /><span className="name">Comments</span></NavLink>

            <div className="left-divider"/>

            <NavLink href={`/console/${subdomain}/theme`}><Palette /><span className="name">Theme</span></NavLink>

            <NavLink href={`/console/${subdomain}/billing`}>
                <Coin />
                <span className="name">Billing</span>
                <span className="mark">
                    {
                        blog.is_on_trial && !blog.subscribed ?
                        <span className="trial-days-left">{trialDaysDiff} days left</span> : null
                    }
                    {
                        !blog.subscribed && !blog.is_on_trial ?
                        <span className="trial-days-left red">Upgrade Required</span> : null
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

            <NavLink href={`/console/${subdomain}/settings`}><Gear/><span className="name">Settings</span></NavLink>

        </div>
    </div>
}