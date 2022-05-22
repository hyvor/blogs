import { useValues } from 'kea';
import React from 'react';
import subdomainLogic from '../logic/subdomainLogic';
import NavLink from '../ReusableComponents/NavLink';

export default function Comments() 
{
    const { subdomain } = useValues(subdomainLogic); 

    return <div className="comments">
        <div>Hyvor comments</div>
    </div>
}