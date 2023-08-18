import { useActions } from "kea";
import { router } from "kea-router";
import { useRef, useEffect, Fragment } from "react";
import { UserRole } from "../enums";
import getSubdomain from "../logic-helpers/subdomain";
import UserPermissions from "../services/UserPermissions";
import NavLink from "./NavLink";
import React from "react";

interface SettingsLinkProps {
    path: string,
    role?: UserRole.OWNER | UserRole.ADMIN | UserRole.EDITOR,
    name: string,
    dividing?: boolean,
    pannelName?: string,
    toolsPrefix?: boolean,
    setPannel: Function
}

function SettingsLink({ path, role = UserRole.ADMIN, name, dividing = false, setPannel, pannelName, toolsPrefix = false }: SettingsLinkProps) {

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

export default SettingsLink;