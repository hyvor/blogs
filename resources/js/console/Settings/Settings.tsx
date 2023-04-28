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
import Highlight from "./Highlight";
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
import Export from "./Export/Export";


interface SettingsSelectProps {
    name: string,
    value: string | number | null,
    options: {
        value: string | number;
        label: string | ReactNode
    }[],
    setPannel: Function,
}

function SettingsSelect({ name, value, options, setPannel }: SettingsSelectProps) {
    const SingleValue = (p: any) => {
        const value = p.data.label.props ? p.data.label.props.name : p.data.label;
        return <components.SingleValue {...p}>
            <div className='posts-filter-row'>
                <div className='posts-filter-name'>{name}</div>
                <div className='posts-filter-value'>{value}</div>
            </div>
        </components.SingleValue>
    };

    const valueCalculated = options.find(i => i.value === value) || options[0];

    return <Select
        value={valueCalculated}
        options={options}
        onChange={(v: any) => setPannel(v.value)}
        components={{ SingleValue }}
    />
}

export default function Settings({ type }: { type: string | undefined }) {
    const blog = getUserBlogBlog();
    const [pannel, setPannel] = useState(type || 'general');
    const pannelOption = [
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

    console.log('Pannel', pannel);

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
        case 'export':
            Type = () => <Export />;
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
                    <SettingsLink path="/shopify" name="Shopify Guide" dividing={true} setPannel={setPannel} />
                }

                <SettingsLink path="" name="General" setPannel={setPannel} />
                <SettingsLink path="/users" name="Users" setPannel={setPannel} />
                <SettingsLink role={UserRole.EDITOR} path="/tags" name="Tags" setPannel={setPannel} />
                <SettingsLink path="/languages" name="Languages" setPannel={setPannel} />

                <div />

                <SettingsLink path="/hosting" name="Hosting" setPannel={setPannel} />
                <SettingsLink path="/seo" name="SEO" setPannel={setPannel} />
                <SettingsLink path="/color-mode" name="Light & Dark Modes" pannelName={'color-mode'} setPannel={setPannel} />
                <SettingsLink path="/navigation" name="Navigation" setPannel={setPannel} />
                <SettingsLink path="/media" name="Media" setPannel={setPannel} />
                <SettingsLink path="/redirects" name="Redirects" setPannel={setPannel} />
                <SettingsLink path="/routes" name="Routes" setPannel={setPannel} />
                <SettingsLink path="/api-keys" name="API Keys" setPannel={setPannel} pannelName={'api-keys'} />
                <SettingsLink path="/webhooks" name="Webhooks" setPannel={setPannel} />

                <div />
                <SettingsLink path="/comments" name="Comments & Newsletter" pannelName={'comments'} setPannel={setPannel} />
                <SettingsLink path="/code" name="Custom Code" pannelName={'code'} setPannel={setPannel} />
                <SettingsLink path="/highlight" name="Syntax Highlighting" pannelName={'highlight'} setPannel={setPannel} />

                <div />
                <SettingsLink path="/export" name="Export" pannelName={'export'} setPannel={setPannel} />
                <SettingsLink role={UserRole.OWNER} path="/danger" pannelName={'danger'} name="Danger Zone" setPannel={setPannel} />
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

interface SettingsLinkProps {
    path: string,
    role?: UserRole.OWNER | UserRole.ADMIN | UserRole.EDITOR,
    name: string,
    dividing?: boolean,
    pannelName?: string,
    setPannel: Function
}

function SettingsLink({ path, role = UserRole.ADMIN, name, dividing = false, setPannel, pannelName }: SettingsLinkProps) {

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

    return <Fragment>
        <NavLink
            ref={ref}
            href={settingsPrefix + path}
            exact={1}
            className={cls}
            onClick={() => {
                if (pannelName)
                    setPannel(pannelName);
                else
                    setPannel(name.toLocaleLowerCase())
            }}
        > {name}</NavLink>

        {dividing && <div />}
    </Fragment >

}
