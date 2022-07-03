import React from 'react';
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

                <NavLink href={settingsPrefix} exact={1}>General</NavLink>
                <NavLink href={settingsPrefix + "/users"}>Users</NavLink>
                <NavLink href={settingsPrefix + "/tags"}>Tags</NavLink>

                <div />

                <NavLink href={settingsPrefix + "/hosting"}>Hosting</NavLink>
                <NavLink href={settingsPrefix + "/seo"}>SEO</NavLink>
                <NavLink href={settingsPrefix + "/color-mode"}>Light & Dark Modes</NavLink>
                <NavLink href={settingsPrefix + "/navigation"}>Navigation</NavLink>
                <NavLink href={settingsPrefix + "/media"}>Media</NavLink>
                <NavLink href={settingsPrefix + "/redirects"}>Redirects</NavLink>
                <NavLink href={settingsPrefix + "/languages"}>Languages</NavLink>
                <NavLink href={settingsPrefix + "/routes"}>Routes</NavLink>
                <NavLink href={settingsPrefix + "/api-keys"}>API Keys</NavLink>
                <NavLink href={settingsPrefix + "/webhooks"}>Webhooks</NavLink>

                <div />
                <NavLink href={settingsPrefix + "/comments"}>Comments & Newsletter</NavLink>
                <NavLink href={settingsPrefix + "/code"}>Custom Code</NavLink>
                <NavLink href={settingsPrefix + "/highlight"}>Syntax Highlighting</NavLink>

                <div />
                <NavLink href={settingsPrefix + "/migrate"}>Import & Export</NavLink>
                <NavLink href={settingsPrefix + "/danger"}>Danger Zone</NavLink>
            </div>
        </div>
        <div className="box box-right settings-right">
            <Type />
        </div>
    </div>

}
