import { useValues } from "kea";
import React, { useState } from "react";
import { BrushFill, CaretDownFill, CaretRightFill } from "react-bootstrap-icons";
import subdomainLogic from "../logic/subdomainLogic";
import themeLogic from "../logic/themeLogic";
import Loader from "../ReusableComponents/Loader";
import File from "./File";
import FileBrowser from "./FileBrowser";

export default function Theme({type}) {

    const { subdomain } = useValues(subdomainLogic);
    const { loadFilesAjax, files } = useValues(themeLogic({subdomain}));

    const themePrefix = `/console/${subdomain}/theme`;

    return <div className="posts-view theme-view">
        <div className="box box-left">
            <div className="middle-heading">
                Theme&nbsp;&nbsp;<a 
                    className="button small"
                    href="/themes"
                    target="_blank"
                >Change</a>
            </div>

            <div className="theme-left-wrap">
                <div className="theme-folders">
                    {
                        loadFilesAjax.status === 'loading' ?
                        <Loader padding={40} /> :

                        <div>
                            <Folder name="templates" />
                            <Folder name="styles" />
                            <Folder name="assets" />
                            <Folder name="lang" />
                            <Folder name={null} />
                        </div>
                    }
                </div>
            </div>
        </div>
        <div className="box box-right theme-right">
            <FileBrowser />
        </div>
    </div>

}

function Folder( {name} ) {

    const { subdomain } = useValues(subdomainLogic);
    const { findFilesOfFolder } = useValues(themeLogic({subdomain}));

    const [unfolded, setUnfolded] = useState(name ? false : true);

    const files = findFilesOfFolder(name);

    return <div className={"folder " + (name || 'root')}>
        {
            name ?
            <div className="folder-name" onClick={() => setUnfolded(!unfolded)}>
                
                <span className="fold-icon">
                    {
                        unfolded ?
                            <CaretDownFill /> :
                            <CaretRightFill />
                    }
                </span>
                <span className="name">{name}</span>
            </div>
        : null }
        <div className={"folder-files" + (unfolded ? " unfolded" : "")}>
            {
                files.map((file) => <File key={file.id} id={file.id} name={file.name} /> )
            }
        </div>
    </div>

}