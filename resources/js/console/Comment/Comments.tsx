import { useValues } from 'kea';
import React from 'react';
import subdomainLogic from '../logic/subdomainLogic';
import NavLink from '../ReusableComponents/NavLink';

export default function Comments() 
{
    const { subdomain } = useValues(subdomainLogic); 

    return <div className="comments box">
        <div style={{
            flex: 1,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 24,
            color: "#777"
        }}>Comments moderating is coming soon!</div>
    </div>
}