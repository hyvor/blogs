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

    const [isPopupShown, setIsPopupShown] = useState(false);

    const isShopify = blog.integration === 'shopify';

    function handleClick(e: MouseEvent<HTMLAnchorElement>) {

        e.preventDefault();
        e.stopPropagation()

        if (isShopify) {
            setIsPopupShown(!isPopupShown);
        } else {
            window.open(blog.base_url, '_blank');
        }
    }

    return <span className="blog-link">
        <span
            onClick={handleClick}
        >
            {children}
        </span>

        {
            isPopupShown &&
            <PopupNotice
                title="Shopify Integartion"
                text={
                    <div>
                        This blog is integrated with a Shopify store. Visit <b>Store URL + /a/blog</b> to view your blog. <span
                            className="link"
                            onClick={() => window.open("/docs/shopify", '_blank')}
                        >Learn more</span>.
                    </div>
                }
                name="Close"
                onClick={() => setIsPopupShown(false)}
            />
        }

    </span>

}