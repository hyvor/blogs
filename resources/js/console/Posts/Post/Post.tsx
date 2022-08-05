import React from 'react';
import Loader from '../../ReusableComponents/Loader';
import Tooltip from '../../ReusableComponents/Tooltip';
import {usePostValues} from "./helpers";
import useSave from './useSave'
import PostTop from "./PostTop/PostTop";
import PostBottom from "./PostBottom";
import PostMiddle from "./PostMiddle";
import Unpublisher from "./Unpublisher";

export default function Post( { id }: { id: number }) {

    const { loadPostAjax, editorState } = usePostValues(id)

    useSave(id);

    if (loadPostAjax.status === 'loading') {
        return <div className="post-loading">
            <Loader />
        </div>;
    }

    return <div className={"post-editor" + (editorState.isFullscreen ? " fullscreen" : "") }>

        <div className="pos-rel"> {/* this element is required to make the tooltip work correctly */}

            <PostTop id={id} />
            <PostMiddle id={id} />
            <PostBottom id={id} />


            <Unpublisher id={id} />

            <Tooltip place="bottom" />

        </div>

    </div>

}
