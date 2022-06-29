import { useActions, useValues } from 'kea';
import React from 'react'
import subdomainLogic from '../logic/subdomainLogic';
import themeLogic from '../logic/themeLogic';

export default function File({ id, name }) {

    const { subdomain } = useValues(subdomainLogic);
    const { editorOpenFile } = useActions(themeLogic({subdomain}));

    return <div 
        className="file"
        onClick={() => editorOpenFile(id)}
    >{name}</div>

}