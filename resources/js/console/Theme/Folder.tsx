import {ThemeFolder} from "../types";
import {useValues} from "kea";
import themeLogic from "../logic/themeLogic";
import getSubdomain from "../logic-helpers/subdomain";
import React, {useState} from "react";
import {CaretDownFill, CaretRightFill} from "react-bootstrap-icons";
import File from "./File";
import NewFileCreator from "./NewFileCreator";

export default function Folder( {name} : {name: ThemeFolder} ) {

    const { findFilesOfFolder } = useValues(themeLogic({subdomain: getSubdomain()}));

    const [unfolded, setUnfolded] = useState(!name);

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
            <NewFileCreator folder={name} />
            {
                files.map((file) => <File key={file.id} id={file.id} name={file.name} /> )
            }
        </div>
    </div>

}