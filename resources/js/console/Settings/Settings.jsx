import { useValues } from 'kea';
import React from 'react';
import subdomainLogic from '../logic/subdomainLogic';
import NavLink from '../ReusableComponents/NavLink';

export default function Settings({type}) {

    const { subdomain } = useValues(subdomainLogic); 
    const settingsPrefix = `/console/${subdomain}/settings`;

    return <div className="posts-view settings-view">
        <div className="box box-left">
            <div className="middle-heading">Settings</div>
            <div className="settings-nav">

                <NavLink href={settingsPrefix} exact={1}>General</NavLink>
                <NavLink href={settingsPrefix + "/users"}>Users</NavLink>
                <NavLink href={settingsPrefix + "/tags"}>Tags</NavLink>
                <NavLink href={settingsPrefix + "/navigation"}>Navigation</NavLink>

                <div></div>
                <NavLink href={settingsPrefix + "/seo"}>SEO</NavLink>
                <NavLink href={settingsPrefix + "/redirects"}>Redirects</NavLink>
                <NavLink href={settingsPrefix + "/webhooks"}>Webhooks</NavLink>
                <NavLink href={settingsPrefix + "/code" }>Custom Code</NavLink>

                <div></div>
                <NavLink href={settingsPrefix + "/import"}>Import</NavLink>
                <NavLink href={settingsPrefix + "/export"}>Export</NavLink>

                <div></div>
                <NavLink href={settingsPrefix + "/delete"}>Delete Blog</NavLink>
            </div>
        </div>
        <div className="box box-right">
            
        </div>
    </div>

}