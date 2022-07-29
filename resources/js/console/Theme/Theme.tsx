import { useValues } from "kea";
import React  from "react";
import themeLogic from "../logic/themeLogic";
import Loader from "../ReusableComponents/Loader";
import getSubdomain from "../logic-helpers/subdomain";
import Download from "./Download";
import Upload from "./Upload";
import Changer from "./Changer";
import Folder from "./Folder";
import FileEditor from "./FileEditor";

export default function Theme() {

    const subdomain = getSubdomain();
    const { loadFilesAjax, files } = useValues(themeLogic({subdomain}));

    const themePrefix = `/console/${subdomain}/theme`;

    return <div className="posts-view theme-view">
        <div className="box box-left">
            <div className="middle-heading">
                Theme&nbsp;&nbsp;<Changer />
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

            <div className="theme-bottom">
                <Upload />
                <Download />
            </div>
        </div>
        <div className="box box-right theme-right">
            <FileEditor />
        </div>
    </div>

}