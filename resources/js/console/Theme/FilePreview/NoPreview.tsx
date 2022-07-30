import React from "react";
import {ThemeFile} from "../../types";
import {getAssetUrl} from "./AssetImagePreview";
import {Link45deg} from "react-bootstrap-icons";

export default function NoPreview({file} : {file: ThemeFile}) {

    const url = getAssetUrl(file.name);

    return <div className="no-preview">
        <div>
            No Preview Available
        </div>
        {
            file.folder === 'assets' &&
            <div className="open-url">
                <a href={url} target="_blank">Open in new tab <Link45deg/></a>
            </div>
        }
    </div>

}