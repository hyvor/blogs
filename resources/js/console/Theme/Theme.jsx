import { useValues } from "kea";
import React, { useState } from "react";
import subdomainLogic from "../logic/subdomainLogic";
import themeLogic from "../logic/themeLogic";
import Loader from "../ReusableComponents/Loader";
import File from "./File";
import FileBrowser from "./FileBrowser";

export default function Theme() {

    const { subdomain } = useValues(subdomainLogic);
    const { loadFilesAjax, files } = useValues(themeLogic({subdomain}));

    return <div className="posts-view theme-view">
        <div className="box box-left">
            <div className="middle-heading">Theme</div>
            <div className="theme-folders">
                {
                    loadFilesAjax.status === 'loading' ?
                    <Loader spacing={40} /> :

                    <div>
                        <Folder name="templates" />
                        <Folder name="styles" />
                        <Folder name="assets" />
                        <Folder name="lang" />
                        <Folder name={null} />
                    </div>
                }
            </div>
            <div className="current-theme"></div>
        </div>
        <div className="box box-right theme-right">
            <FileBrowser />
        </div>
    </div>

}

function Folder( {name} ) {

    const { subdomain } = useValues(subdomainLogic);
    const { findFilesOfFolder } = useValues(themeLogic({subdomain}));

    const files = findFilesOfFolder(name);

    return <div className="folder">
        <div className="folder-name">{name}</div>
        <div className="folder-files">
            {
                files.map((file) => <File key={file.id} id={file.id} name={file.name} /> )
            }
        </div>
    </div>

}