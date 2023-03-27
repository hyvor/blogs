import React, { FC, ReactNode, useEffect, useRef } from 'react';
import BlogsSelector from './BlogsSelector';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import NavLink from '../ReusableComponents/NavLink';
import userBlogsLogic from '../logic/userBlogsLogic';
import {
    BoxArrowUpRight,
    Chat, Coin,
    Exclamation,
    Files, Gear,
    House, Megaphone,
    Palette,
    Pencil,
} from 'react-bootstrap-icons';
import dayjs from 'dayjs';
import { appConfig } from "../helpers";
import { router } from "kea-router";
import UserPermissions from "../services/UserPermissions";
import BlogLink from "../BlogPreview/BlogLink";

export default function Left() {

    const { subdomain } = useValues(subdomainLogic);

    if (!subdomain) {
        return null;
    }

    return <LeftInner subdomain={subdomain} />

}

function LeftInner({subdomain} : {subdomain: string}) {

    const { findBlogBySubdomain } = useValues(userBlogsLogic);
    const { blog, blog: { subscription: currentSubscription } } = findBlogBySubdomain(subdomain);

    useEffect(() => {
        (window as any).FeaturebaseWidget && (window as any).FeaturebaseWidget.init({
            organization: "hyvorblogs",
            initialPage: "MainView",
            // placement: "left",
            fullScreen: true
        })
    }, []);

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

            <LeftLink
                path=""
                icon={<House />}
                name="Blog"
                extra={
                    <span className="mark">
                        <BlogLink><BoxArrowUpRight /></BlogLink>
                    </span>
                }
            />

            <div className="left-divider" />

            <LeftLink path="/posts" icon={<Pencil />} name="Posts" permission={UserPermissions.canAccessPosts} />
            <LeftLink path="/pages" icon={<Files />} name="Pages" permission={UserPermissions.canAccessPages} />
            <LeftLink path="/comments" icon={<Chat />} name="Comments" permission={UserPermissions.canAccessComments} />

            <div className="left-divider" />


            <LeftLink path="/theme" icon={<Palette />} name="Theme" permission={UserPermissions.canAccessTheme} />

            <LeftLink path="/billing" icon={<Coin />} name="Billing"
                      permission={UserPermissions.canAccessBilling}
                      extra={
                          <span className="mark">
                        {
                            currentSubscription &&
                            currentSubscription.status === 'past_due'
                                ?
                                <span className="subscription-issue-icon">
                                    <Exclamation />
                                </span>
                                : null
                        }
                    </span>
                      }
            />

            <LeftLink path="/settings" icon={<Gear />} name="Settings" permission={UserPermissions.canAccessSettings} />

        </div>

        <div id="left-bottom">
            <div className="changes-item box" onClick={() => {
                window.postMessage({
                    target: 'FeaturebaseWidget',
                    data: { action: 'toggleWidget' },
                })
            }}>
                <Megaphone /> Changes & Feedback <span id="fb-update-badge"></span>
            </div>
        </div>

    </div>

}

function LeftLink({ path, icon, name, extra = null, permission }: LeftLinkProps) {

    const { subdomain } = useValues(subdomainLogic);
    const { push } = useActions(router);
    const perm = typeof permission === 'function' ? permission() : true;

    const ref = useRef<HTMLAnchorElement | null>(null);

    useEffect(() => {

        /**
         * Redirect the user to Blog Preview when accessing unauthorized routes via the direct URL
         * Just a simple check
         */
        const link = ref.current as HTMLAnchorElement
        if (link.classList.contains('global-no-permissions') && link.classList.contains('active')) {
            push('/console/' + subdomain);
        }

    }, []);

    return <NavLink
        ref={ref}
        href={`/console/${subdomain}${path}`}
        exact={path === '' ? 1 : 0}
        className={"nav-link" + (!perm ? "global-no-permissions" : "")}
        data-attr={"main-nav-" + name.toLowerCase()}
    >{icon}<span className="name">{name}</span>{extra}</NavLink>
}


type LeftLinkProps = {
    path: string,
    icon: ReactNode,
    name: string,
    extra?: ReactNode,
    permission?: Function
};
