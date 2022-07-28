import React, {useEffect, useRef} from 'react';
import NavLink from '../ReusableComponents/NavLink';
import Code from './Code';
import SettingsMedia from './Media/SettingsMedia';
import SettingsMigrate from './SettingsMigrate';
import Users from './Users/Users';
import SettingRedirects from './Redirects/Redirects';
import Comments from './Comments';
import Navigations from './Navigation/Navigations';
import Tags from './Tags/Tags';
import SettingsRoutes from './Routes/Routes';
import Languages from './Languages/Languages';
import SettingsGeneral from './General/SettingsGeneral';
import Hosting from './Hosting';
import SEO from './SEO';
import ColorMode from "./ColorMode";
import Highlight from "./Highlight";
import getSubdomain from "../logic-helpers/subdomain";
import Webhooks from "./Webhooks";
import ApiKeys from "./ApiKeys/ApiKeys";
import Danger from "./Danger/Danger";
import {UserRole} from "../enums";
import UserPermissions from "../services/UserPermissions";
import {useActions} from "kea";
import {router} from "kea-router";

export default function Settings({type} : {type: string | undefined}) {

    const subdomain = getSubdomain();
    const settingsPrefix = `/console/${subdomain}/settings`;

    let Type = () => <SettingsGeneral />;
    switch (type) {
        case 'users':
            Type = () => <Users />;
            break;
        case 'tags':
            Type = () => <Tags />;
            break;
        case 'navigation':
            Type = () => <Navigations />;
            break;
        case 'hosting':
            Type = () => <Hosting />;
            break;
        case 'seo':
            Type = () => <SEO />;
            break;
        case 'redirects':
            Type = () => <SettingRedirects />;
            break;
        case 'media':
            Type = () => <SettingsMedia />;
            break;
        case 'code':
            Type = () => <Code />
            break;
        case 'comments':
            Type = () => <Comments />
            break;
        case 'migrate':
            Type = () => <SettingsMigrate />;
            break;
        case 'danger':
            Type = () => <Danger />;
            break;
        case 'routes':
            Type = () => <SettingsRoutes />;
            break;
        case 'languages':
            Type = () => <Languages />;
            break;
        case 'color-mode':
            Type = () => <ColorMode />;
            break;
        case 'highlight':
            Type = () => <Highlight />;
            break;
        case 'webhooks':
            Type = () => <Webhooks />
            break;
        case 'api-keys':
            Type = () => <ApiKeys />
            break;
    }

    return <div className="posts-view settings-view">
        <div className="box box-left">
            <div className="middle-heading">Settings</div>
            <div className="settings-nav">

                <SettingsLink path="" name="General" />
                <SettingsLink path="/users" name="Users" />
                <SettingsLink role={UserRole.EDITOR} path="/tags" name="Tags" />

                <div />

                <SettingsLink path="/hosting" name="Hosting" />
                <SettingsLink path="/seo" name="SEO" />
                <SettingsLink path="/color-mode" name="Light & Dark Modes" />
                <SettingsLink path="/navigation" name="Navigation" />
                <SettingsLink path="/media" name="Media" />
                <SettingsLink path="/redirects" name="Redirects" />
                <SettingsLink path="/languages" name="Languages" />
                <SettingsLink path="/routes" name="Routes" />
                <SettingsLink path="/api-keys" name="API Keys" />
                <SettingsLink path="/webhooks" name="Webhooks" />

                <div />
                <SettingsLink path="/comments" name="Comments & Newsletter" />
                <SettingsLink path="/code" name="Custom Code" />
                <SettingsLink path="/highlight" name="Syntax Highlighting" />

                <div />
                <SettingsLink path="/migrate" name="Import & Export" />
                <SettingsLink role={UserRole.OWNER} path="/danger" name="Danger Zone" />
            </div>
        </div>
        <div className="box box-right settings-right">
            <Type />
        </div>
    </div>

}

interface SettingsLinkProps {
    path: string,
    role?: UserRole.OWNER | UserRole.ADMIN | UserRole.EDITOR,
    name: string
}

function SettingsLink({ path, role = UserRole.ADMIN, name } : SettingsLinkProps) {

    const subdomain = getSubdomain();
    const settingsPrefix = `/console/${subdomain}/settings`;
    const { push } = useActions(router);

    const userRole = UserPermissions.getRole();
    const ref = useRef<HTMLAnchorElement | null>(null);

    const roles = {
        [UserRole.EDITOR]: [UserRole.EDITOR],
        [UserRole.ADMIN]: [UserRole.EDITOR, UserRole.ADMIN],
        [UserRole.OWNER]: [UserRole.EDITOR, UserRole.ADMIN, UserRole.OWNER]
    }

    let cls = undefined;
    // @ts-ignore
    const availableRoles = roles[userRole];
    if (!availableRoles || availableRoles.indexOf(role) < 0) {
        cls = 'global-no-permissions'
    }

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
        href={settingsPrefix + path}
        exact={1}
        className={cls}
    >{ name }</NavLink>

}
