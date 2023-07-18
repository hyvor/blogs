import React from 'react';
import { router } from 'kea-router'
import { useValues } from 'kea';

const NavLink = React.forwardRef((props: any = {}, ref) => {

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
        ref={ref}
        onClick={(event) => {
            if (props.disabled) {
                event.preventDefault();
                props.onClick && props.onClick(event);
                return;
            }
            if (!props.target) {
                event.preventDefault()
                router.actions.push(props.href) // router is mounted automatically, so this is safe to call
            }
            props.onClick && props.onClick(event)
        }}
        className={clsName}
    />

});

export default NavLink