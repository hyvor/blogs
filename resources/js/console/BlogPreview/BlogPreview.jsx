import { useValues } from 'kea';
import React, { useState } from 'react'
import subdomainLogic from '../logic/subdomainLogic';
import { BoxArrowUpRight, Laptop, Phone } from 'react-bootstrap-icons';
import Loader from '../ReusableComponents/Loader';


export default function BlogPreview() {

    const { subdomain } = useValues(subdomainLogic);
    const [type, setType] = useState('laptop');

    const [isLoading, setIsLoading] = useState(true);

    function closeLoading() {
        setIsLoading(false);
    }

    var domain = window.appConfig.domains.delivery;
    var protocol = domain.match(/\.test/) ? 'http' : 'https';

    return <div className="box blog-preview-view">
        <div className="navi">
            <div className="left">
                <a 
                    href={ `https://${subdomain}.${domain}` }
                    target="_blank"
                >{subdomain}.hyvorblogs.io &nbsp;<BoxArrowUpRight /></a>
            </div>
            <div className="right">
                <span onClick={() => setType('laptop')} className={type == 'laptop' ? "active" : ""}><Laptop /></span>
                <span onClick={() => setType('phone')} className={type == 'phone' ? "active" : ""}><Phone /></span>
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
                src={`${protocol}://${subdomain}.${domain}`} 
                style={{
                    width: type === 'laptop' ? "100%" : 360,
                    height: type === 'laptop' ? "100%" : 740,
                    display: isLoading ? "none" : "block"
                }}
                onLoad={closeLoading}
            />
        </div>
    </div>;

}