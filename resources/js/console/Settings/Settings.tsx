import { useValues } from 'kea';
import React from 'react';
import subdomainLogic from '../logic/subdomainLogic';
import NavLink from '../ReusableComponents/NavLink';
import SettingsDelete from './SettingsDelete';
import Code from './Code';
import SettingsMedia from './Media/SettingsMedia';
import SettingsMigrate from './SettingsMigrate';
import SettingsUsers from './Users/SettingsUsers';
import SettingRedirects from './Redirects/Redirects';
import Comments from './Comments';
import SettingNavigation from './Navigation/SettingNavigation';
import Tags from './Tags/Tags';
import SettingsRoutes from './SettingsRoutes';
import SettingsLanguages from './SettingsLanguages';
import SettingsGeneral from './General/SettingsGeneral';
import Hosting from './Hosting';
import SEO from './SEO';
import ColorMode from "./ColorMode";
import Highlight from "./Highlight";

export default function Settings({type} : {type: string}) {

    const { subdomain } = useValues(subdomainLogic); 
    const settingsPrefix = `/console/${subdomain}/settings`;

    var Type = () => <SettingsGeneral />;
    switch (type) {
        case 'users':
            Type = () => <SettingsUsers />;
            break;
        case 'tags':
            Type = () => <Tags />;
            break;
        case 'navigation':
            Type = () => <SettingNavigation />;
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
        case 'delete':
            Type = () => <SettingsDelete />;
            break;
        case 'routes':
            Type = () => <SettingsRoutes />;
            break;
        case 'languages':
            Type = () => <SettingsLanguages />;
            break;
        case 'color-mode':
            Type = () => <ColorMode />;
            break;
        case 'highlight':
            Type = () => <Highlight />;
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
                <NavLink href={settingsPrefix + "/webhooks"}>Webhooks</NavLink>

                <div />
                <NavLink href={settingsPrefix + "/comments"}>Comments & Newsletter</NavLink>
                <NavLink href={settingsPrefix + "/code"}>Custom Code</NavLink>
                <NavLink href={settingsPrefix + "/highlight"}>Syntax Highlighting</NavLink>

                <div />
                <NavLink href={settingsPrefix + "/migrate"}>Import & Export</NavLink>
                <NavLink href={settingsPrefix + "/delete"}>Delete Blog</NavLink>
            </div>
        </div>
        <div className="box box-right settings-right">
            <Type />
        </div>
    </div>

}
