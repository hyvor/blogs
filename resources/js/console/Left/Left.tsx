import React, {FC, ReactNode} from 'react';
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
import {
    canAccessBilling,
    canAccessComments,
    canAccessPages,
    canAccessPosts, canAccessSettings,
    canAccessTheme
} from "../services/permissions";

export default function Left() {

    const { subdomain } = useValues(subdomainLogic);
    if (!subdomain) {
        return null;
    }

    const { findBlogBySubdomain } = useValues(blogsLogic);
    const { blog, blog: { subscription: currentSubscription } } : UserBlog  = findBlogBySubdomain(subdomain);

    const trialDaysDiff = blog.is_on_trial ?  dayjs.unix(blog.trial_ends_at).diff(dayjs(), 'd') : 0;


    function LeftLink({path, icon, name, extra = null, permission} : LeftLinkProps) {
        const perm = typeof permission === 'function' ? permission() : true;
        return <NavLink
            href={`/console/${subdomain}${path}`}
            exact={path === ''}
            className={!perm ? "no-perm" : ""}
        >{icon}<span className="name">{name}</span>{extra}</NavLink>
    }
    
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
                        src={appConfig().hyvorUser.picture_url}
                        className="round-image-40"
                        alt="Profile Picture"
                    />
                </a>
            </div>
        </div>


        <div id="left-nav" className="box">
            <BlogsSelector />

            <LeftLink path="" icon={<House />} name="Blog" />

            <div className="left-divider"/>

            <LeftLink path="/posts" icon={<Pencil />} name="Posts" permission={canAccessPosts} />
            <LeftLink path="/pages" icon={<Files />} name="Pages" permission={canAccessPages} />
            <LeftLink path="/comments" icon={<Chat />} name="Comments" permission={canAccessComments} />

            <div className="left-divider"/>


            <LeftLink path="/theme" icon={<Palette />} name="Theme" permission={canAccessTheme} />

            <LeftLink path="/billing" icon={<Coin />} name="Billing"
                permission={canAccessBilling}
                extra={
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
                }
            />

            <LeftLink path="/settings" icon={<Gear />} name="Settings" permission={canAccessSettings} />

        </div>
    </div>
}

type LeftLinkProps = {
    path: string,
    icon: ReactNode,
    name: string,
    extra?: ReactNode,
    permission?: Function
};
