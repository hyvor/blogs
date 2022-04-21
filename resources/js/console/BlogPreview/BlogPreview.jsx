import { useValues } from 'kea';
import React, { useState } from 'react'
import subdomainLogic from '../logic/subdomainLogic';
import blogsLogic from '../logic/blogsLogic'
import { BoxArrowUpRight, Laptop, Phone, Tablet } from 'react-bootstrap-icons';
import Loader from '../ReusableComponents/Loader';


export default function BlogPreview() {

    const { subdomain } = useValues(subdomainLogic);
    const { findBlogBySubdomain } = useValues(blogsLogic)
    const [type, setType] = useState('laptop');

    const [isLoading, setIsLoading] = useState(true);

    function closeLoading() {
        setIsLoading(false);
    }

    var domain = window.appConfig.domains.delivery;

    const blog = findBlogBySubdomain(subdomain)

    return <div className="box blog-preview-view">
        <div className="navi">
            <div className="left">
                <a 
                    href={ blog.blog.base_url }
                    target="_blank"
                >{ blog.blog.base_url.replace(/^https?:\/\//, '') } &nbsp;<BoxArrowUpRight /></a>
            </div>
            <div className="right">
                <span onClick={() => setType('laptop')} className={type == 'laptop' ? "active" : ""}><Laptop /></span>
                <span onClick={() => setType('tablet')} className={type == 'tablet' ? "active" : ""}><Tablet /></span>
                <span onClick={() => setType('phone')} className={type == 'phone' ? "active" : ""}><Phone size={14} /></span>
            </div>
        </div>
        <div 
            className="iframe"
            style={{
                padding: type === 'laptop' ? 0 : 15
            }}
        >
            {
                isLoading ?
                <Loader /> : null 
            }
            <iframe
                id="preview-iframe"
                src={ blog.blog.base_url }
                style={{
                    width: type === 'laptop' ? "100%" : (type === 'tablet' ? 600 : 360),
                    height: type === 'laptop' ? "100%" : 740,
                    display: isLoading ? "none" : "block"
                }}
                onLoad={closeLoading}
            />
        </div>
    </div>;

}
