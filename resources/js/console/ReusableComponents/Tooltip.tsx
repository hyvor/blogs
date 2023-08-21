import React from "react";
import { ReactNode } from "react";

export default function Tooltip(
    { children, tooltip } : 
    { 
        children: ReactNode,
        tooltip: ReactNode
    }
) {

    const wrapRef = React.useRef<HTMLSpanElement>(null);
    const tooltipRef = React.useRef<HTMLDivElement>(null);

    function handleMouseEnter() {

        // position tooltip
        const wrapRect = wrapRef.current?.getBoundingClientRect();
        const tooltipRect = tooltipRef.current?.getBoundingClientRect();

        if (wrapRect && tooltipRect) {

            const top = wrapRect.top - tooltipRect.height - 10;
            const left = wrapRect.left + wrapRect.width/2 - tooltipRect.width/2;

            tooltipRef.current!.style.top = top + 'px';
            tooltipRef.current!.style.left = left + 'px';

        }


    }


    return <span 
        className="global-tooltip"
        onMouseEnter={handleMouseEnter}
        ref={wrapRef}
    >
        {children}

        {
            tooltip !== null &&
            <div className="tooltip" ref={tooltipRef}>
                {tooltip}
            </div>
        }
    </span>

}