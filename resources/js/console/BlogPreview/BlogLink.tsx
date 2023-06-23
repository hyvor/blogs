import getSubdomain from "../logic-helpers/subdomain";
import userBlogsLogic from "../logic/userBlogsLogic";
import {useValues} from "kea";
import {MouseEvent, ReactNode, useState} from "react";
import React from "react";
import {PopupNotice} from "../ReusableComponents/Popup";

export default function BlogLink({ children } : { children: ReactNode }) {

    const subdomain = getSubdomain();
    const { findBlogBySubdomain } = useValues(userBlogsLogic);
    const { blog } = findBlogBySubdomain(subdomain);

    // const [isPopupShown, setIsPopupShown] = useState(false);
    // const isShopify = blog.integration === 'shopify';

    function handleClick(e: MouseEvent<HTMLAnchorElement>) {
        e.preventDefault();
        e.stopPropagation()
        window.open(blog.base_url, '_blank');
    }

    return <span className="blog-link" onClick={handleClick}>
        {children}
    </span>

}