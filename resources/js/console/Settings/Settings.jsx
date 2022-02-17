import { useValues } from 'kea';
import React from 'react';
import subdomainLogic from '../logic/subdomainLogic';
import NavLink from '../ReusableComponents/NavLink';
import SettingsDelete from './SettingsDelete';
import SettingsCode from './SettingsCode';
import SettingsMedia from './SettingsMedia';
import SettingsMigrate from './SettingsMigrate';
import SettingUsers from './SettingUsers';
import SettingRedirects from './SettingRedirects';
import SettingsComments from './SettingsComments';
import SettingNavigations from './SettingNavigations';
import SettingsRoutes from './SettingsRoutes';
import SettingsLanguages from './SettingsLanguages';

export default function Settings({type}) {

    const { subdomain } = useValues(subdomainLogic); 
    const settingsPrefix = `/console/${subdomain}/settings`;

    var Type = () => null;
    switch (type) {
        case 'users':
            Type = () => <SettingUsers />;
            break;
        case 'navigation':
            Type = () => <SettingNavigations />;
            break;
        case 'redirects':
            Type = () => <SettingRedirects />;
            break;
        case 'media':
            Type = () => <SettingsMedia />;
            break;
        case 'code':
            Type = () => <SettingsCode />
            break;
        case 'comments':
            Type = () => <SettingsComments />
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
    }

    return <div className="posts-view settings-view">
        <div className="box box-left">
            <div className="middle-heading">Settings</div>
            <div className="settings-nav">

                <NavLink href={settingsPrefix} exact={1}>General</NavLink>
                <NavLink href={settingsPrefix + "/users"}>Users</NavLink>
                <NavLink href={settingsPrefix + "/tags"}>Tags</NavLink>

                <div></div>
                <NavLink href={settingsPrefix + "/seo"}>SEO</NavLink>
                <NavLink href={settingsPrefix + "/navigation"}>Navigation</NavLink>
                <NavLink href={settingsPrefix + "/media"}>Media</NavLink>
                <NavLink href={settingsPrefix + "/redirects"}>Redirects</NavLink>
                <NavLink href={settingsPrefix + "/languages"}>Languages</NavLink>
                <NavLink href={settingsPrefix + "/routes"}>Routes</NavLink>
                <NavLink href={settingsPrefix + "/webhooks"}>Webhooks</NavLink>

                <div></div>
                <NavLink href={settingsPrefix + "/comments"}>Comments & Newsletter</NavLink>
                <NavLink href={settingsPrefix + "/code"}>Custom Code</NavLink>
                <NavLink href={settingsPrefix + "/highlight"}>Syntax Highlighting</NavLink>

                <div></div>
                <NavLink href={settingsPrefix + "/migrate"}>Import & Export</NavLink>
                <NavLink href={settingsPrefix + "/delete"}>Delete Blog</NavLink>
            </div>
        </div>
        <div className="box box-right settings-right">
            <Type />
        </div>
    </div>

}