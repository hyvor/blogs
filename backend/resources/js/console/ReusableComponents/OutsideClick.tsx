import React from "react";


export function OutsideClick({ children, onClick } : { children: React.ReactNode, onClick: Function }) {

    function handleClick(e: MouseEvent) {
        if (e.target instanceof HTMLElement && !e.target.closest('.outside-click')) {
            onClick();
        }
    }

    React.useEffect(() => {
        document.addEventListener('click', handleClick);
        return () => document.removeEventListener('click', handleClick);
    }, []);

    return <div className="outside-click">{children}</div>

}