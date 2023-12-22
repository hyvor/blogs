import { useActions } from "kea";
import { router } from "kea-router";
import { useRef, useEffect, Fragment, ReactNode } from "react";
import { UserRole } from "../enums";
import getSubdomain from "../logic-helpers/subdomain";
import UserPermissions from "../services/UserPermissions";
import NavLink from "./NavLink";
import React from "react";
import { List } from "react-bootstrap-icons";

interface SettingsLinkProps {
    path: string,
    role?: UserRole.OWNER | UserRole.ADMIN | UserRole.EDITOR,
    name: string,
    dividing?: boolean,
    pannelName?: string,
    toolsPrefix?: boolean,
    setPannel: Function,
    icon?: any,
    extra?: ReactNode
}

function SettingsLink({ path, role = UserRole.ADMIN, name, dividing = false, setPannel, pannelName, toolsPrefix = false, icon = <List />, extra }: SettingsLinkProps) {

    const subdomain = getSubdomain();
    const settingsPrefix = `/console/${subdomain}/${toolsPrefix ? 'tools' : 'settings'}`;
    const { push } = useActions(router);

    const userRole = UserPermissions.getRole();
    const ref = useRef<HTMLAnchorElement | null>(null);

    const roles = {
        [UserRole.EDITOR]: [UserRole.EDITOR],
        [UserRole.ADMIN]: [UserRole.EDITOR, UserRole.ADMIN],
        [UserRole.OWNER]: [UserRole.EDITOR, UserRole.ADMIN, UserRole.OWNER]
    }

    let cls = "nav-link";
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
        >    <div className="settings-icon-row">
                <span className="settings-icon">{icon}</span>
                 <span className="name">{name}</span>

                 {
                    extra && <span className="extra">{extra}</span>
                 }
            </div>
        </NavLink>

        {dividing && <div />}
    </Fragment >

}

export default SettingsLink;