import React from 'react';
import { router } from 'kea-router'
import { useValues } from 'kea';

export default function NavLink(props = {}) {

    const { location: { pathname } } = useValues(router);
    
    const isActive = props.href === pathname.toLowerCase();

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