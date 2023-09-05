import React, { FC, ReactNode, useEffect, useRef, useState } from 'react';
import BlogsSelector from './BlogsSelector';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import NavLink from '../ReusableComponents/NavLink';
import userBlogsLogic from '../logic/userBlogsLogic';
import {
    BoxArrowUpRight,
    Coin, Discord,
    Exclamation,
    Files, Gear,
    House, InfoCircle, Megaphone,
    Palette,
    Pencil, Plugin, Tools,
} from 'react-bootstrap-icons';
import dayjs from 'dayjs';
import { appConfig } from "../helpers";
import { router } from "kea-router";
import UserPermissions from "../services/UserPermissions";
import BlogLink from "../BlogPreview/BlogLink";
import { hasTrialEndedAndNotSubscribed } from '../lib/blog-helpers';
import ReactSwitch from 'react-switch';
import Switch from '../ReusableComponents/Switch';

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

    const [isDark, setIsDark] = useState(false);

    useEffect(() => {
        const storedPreference = localStorage.getItem('prefersDarkMode');
        document.querySelector('body')?.setAttribute('data-theme', storedPreference == 'true' ? 'dark' : 'light');
        setIsDark(storedPreference === 'true');

      }, []);

    const handleDarkMode = (val: boolean | ((prevState: boolean) => boolean)) => {
        localStorage.setItem('prefersDarkMode', val ? 'true' : 'false');
        document.querySelector('body')?.setAttribute('data-theme', val ? 'dark' : 'light');
        setIsDark(val);
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

            <div className="left-divider" />


            <LeftLink path="/theme" icon={<Palette />} name="Theme" permission={UserPermissions.canAccessTheme} />

            <LeftLink path="/billing" icon={<Coin />} name="Billing"
                      permission={UserPermissions.canAccessBilling}
                      extra={
                          <span className="mark">
                        {
                            (currentSubscription && currentSubscription.status === 'past_due') ||
                            hasTrialEndedAndNotSubscribed(subdomain)
                                ?
                                <span 
                                    className="subscription-issue-icon"
                                    data-testid="nav-subscription-issue-icon"
                                >
                                    <Exclamation />
                                </span>
                                : null
                        }
                    </span>
                      }
            />


            <LeftLink
                path="/integrations"
                icon={<Plugin />}
                name="Integrations"
                permission={UserPermissions.canAccessSettings}
            />

            <LeftLink
                path="/tools"
                icon={<Tools />}
                name="Tools"
                permission={UserPermissions.canAccessSettings}
                extra={
                    <span className="global-tag blue">NEW</span>
                }
            />

            <LeftLink path="/settings" icon={<Gear />} name="Settings" permission={UserPermissions.canAccessSettings} />

        </div>


        <div id="left-bottom">
        <div className='dark-mode-row'>
            Dark mode
            <div className='dark-mode-switch'>
                <Switch
                    checked={isDark}
                    onChange={handleDarkMode}
                />
            </div>
        </div>
            <div className="changes-item box" onClick={() => {
                window.postMessage({
                    target: 'FeaturebaseWidget',
                    data: { action: 'toggleWidget' },
                })
            }}>
                <Megaphone /> Changes & Feedback <span id="fb-update-badge"></span>
            </div>

            <a className="bottom-item" href="https://discord.gg/2WRJxQB" target="_blank">
                <span className="icon discord"><Discord /></span>Join our Discord
            </a>

            <a className="bottom-item" href="/docs" target="_blank">
                <span className="icon"><InfoCircle /></span>Docs
            </a>


            <a
                className="bottom-item"
                href="https://community.blogs.hyvor.com/roadmap"
                target="_blank"
            >
                <span className="icon"><Megaphone /></span>Changelog <span id="fb-update-badge"></span>
            </a>

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
        data-testid={"main-nav-" + name.toLowerCase()}
    >{icon}<span className="name">{name}</span>{extra}</NavLink>
}


type LeftLinkProps = {
    path: string,
    icon: ReactNode,
    name: string,
    extra?: ReactNode,
    permission?: Function
};
