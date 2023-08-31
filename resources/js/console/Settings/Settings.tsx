import React, { Fragment, ReactNode, useEffect, useRef, useState } from 'react';
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
import PostContentSettings from "./PostContentSettings";
import getSubdomain from "../logic-helpers/subdomain";
import Webhooks from "./Webhooks/Webhooks";
import ApiKeys from "./ApiKeys/ApiKeys";
import Danger from "./Danger/Danger";
import { UserRole } from "../enums";
import UserPermissions from "../services/UserPermissions";
import { useActions } from "kea";
import { router } from "kea-router";
import { getUserBlogBlog } from "../logic-helpers/blog";
import Shopify from "./Integrations/Shopify";
import Select from '../ReusableComponents/Select';
import { components } from 'react-select';
import SettingsLink from '../ReusableComponents/SettingsLink';
import SettingsSelect from '../ReusableComponents/SettingsSelect';
import { ArrowBarRight, ArrowBarUp, CardText, ChatText, CodeSlash, CursorText, Database, Exclamation, ExclamationTriangle, FileEarmarkPlay, Globe, Key, Lightbulb, List, Lock, People, PeopleFill, Router, SearchHeart, Send, Tag } from 'react-bootstrap-icons';

export default function Settings({ type }: { type: string | undefined }) {
    const blog = getUserBlogBlog();
    const [pannel, setPannel] = useState(type || 'general');
    let pannelOption = [
        { value: 'general', label: 'General' },
        { value: 'users', label: 'Users' },
        { value: 'tags', label: 'Tags' },
        { value: 'languages', label: 'Languages' },
        { value: 'hosting', label: 'Hosting' },
        { value: 'seo', label: 'SEO' },
        { value: 'color-mode', label: 'Light & Dark Modes' },
        { value: 'navigation', label: 'Navigation' },
        { value: 'media', label: 'Media' },
        { value: 'redirects', label: 'Redirects' },
        { value: 'routes', label: 'Routes' },
        { value: 'api-keys', label: 'API Keys' },
        { value: 'webhooks', label: 'Webhooks' },
        { value: 'comments', label: 'Comments & Newsletter' },
        { value: 'code', label: 'Custom Code' },
        { value: 'highlight', label: 'Syntax Highlighting' },
        { value: 'migrate', label: 'Import & Export' },
        { value: 'danger', label: 'Danger Zone' },
    ]

    if (blog.integration === 'shopify') {
        pannelOption = [{ value: 'shopify', label: 'Shopify Guide' }, ...pannelOption];
    }

    let Type = () => <SettingsGeneral />;
    switch (pannel) {
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
        case 'post-content':
            Type = () => <PostContentSettings />;
            break;
        case 'webhooks':
            Type = () => <Webhooks />
            break;
        case 'api-keys':
            Type = () => <ApiKeys />
            break;

        // integrations
        case 'shopify':
            Type = () => <Shopify />
            break;
    }

    return <div className="posts-view settings-view">
        <div className="box box-left">

            <div className="settings-nav">

                {
                    blog.integration === 'shopify' &&
                    <SettingsLink
                        path="/shopify"
                        name="Shopify Guide"
                        dividing={true}
                        setPannel={setPannel}
                        pannelName="shopify"
                    />
                }

                <SettingsLink path="" name="General" setPannel={setPannel} icon={<List />}/>
                <SettingsLink path="/users" name="Users" setPannel={setPannel} icon={<People />}/>
                <SettingsLink role={UserRole.EDITOR} path="/tags" name="Tags" setPannel={setPannel} icon={<Tag />}/>
                <SettingsLink path="/languages" name="Languages" setPannel={setPannel} icon={<Globe />}/>

                <div />

                <SettingsLink path="/hosting" name="Hosting" setPannel={setPannel} icon={<Database />}/>
                <SettingsLink path="/seo" name="SEO" setPannel={setPannel} icon={<SearchHeart />}/>
                <SettingsLink path="/color-mode" name="Light & Dark Modes" pannelName={'color-mode'} setPannel={setPannel} icon={<Lightbulb />}/>
                <SettingsLink path="/post-content" name="Post Content" pannelName={'post-content'} setPannel={setPannel} icon={<CursorText />}/>
                <SettingsLink path="/navigation" name="Navigation" setPannel={setPannel} icon={<ArrowBarRight />}/>
                <SettingsLink path="/media" name="Media" setPannel={setPannel} icon={<FileEarmarkPlay />}/>
                <SettingsLink path="/redirects" name="Redirects" setPannel={setPannel} icon={<ArrowBarUp />}/>
                <SettingsLink path="/routes" name="Routes" setPannel={setPannel} icon={<Router />}/>

                <div />
                <SettingsLink path="/comments" name="Comments & Newsletter" pannelName={'comments'} setPannel={setPannel} icon={<ChatText />}/>
                <SettingsLink path="/code" name="Custom Code" pannelName={'code'} setPannel={setPannel} icon={<CodeSlash />}/>
                <SettingsLink path="/api-keys" name="API Keys" setPannel={setPannel} pannelName={'api-keys'} icon={<Key />}/>
                <SettingsLink path="/webhooks" name="Webhooks" setPannel={setPannel} icon={<Send />}/>

                <div />

                <SettingsLink role={UserRole.OWNER} path="/danger" pannelName={'danger'} name="Danger Zone" setPannel={setPannel} icon={<ExclamationTriangle />}/>
            </div>
        </div>
        <div className="box box-right settings-right">
            <div className='settings-selector'>
                <div className='title'>Settings</div>
                <SettingsSelect name="" value={pannel} options={pannelOption} setPannel={setPannel} />
            </div>

            <Type />
        </div>
    </div>

}
