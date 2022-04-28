import React from 'react';
import { router } from 'kea-router'
import { useValues } from 'kea';

export default function NavLink(props: any = {}) {

    let { location: { pathname } } = useValues(router);

    pathname = pathname.toLowerCase();
    const isActive = props.href.toLowerCase() === pathname ||
        (!props.exact &&
            pathname.startsWith(props.href) &&
            pathname.charAt(props.href.length) === "/");

    let clsName = (props.className || '');
    if (isActive) {
        clsName += " active";
    }

    return <a
        {...props}
        onClick={(event) => {
            if (!props.target) {
                event.preventDefault()
                router.actions.push(props.href) // router is mounted automatically, so this is safe to call
            }
            props.onClick && props.onClick(event)
        }}
        className={clsName}
    />

}