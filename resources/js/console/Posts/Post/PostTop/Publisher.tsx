import React, {useEffect, useRef, useState} from 'react'
import Radio from '../../../ReusableComponents/Radio'
import DatePicker from 'react-datepicker';
import { usePostActions, usePostValues } from '../helpers';
import dayjs from 'dayjs';
import ActionButton from '../../../ReusableComponents/ActionButton';
import { toast } from 'react-toastify';
import {Post, PostVariant} from "../../../types";
import onOutsideClick from "../../../../helpers/onOutsideClick";

let outsideClickListenerRemover : any;

export default function Publisher({id} : {id: number}) {

    const { forceSavePostAjax, editorState  } = usePostValues(id);
    const { forceSavePost, changeEditorState } = usePostActions(id);

    const [hasClicked, setHasClicked] = useState(false)
    const [publishTime, setPublishTime] = useState<Date | null>(null)

    const publisherViewRef = useRef<HTMLDivElement | null>(null)

    function handlePublishTimeChange(setTime: boolean) {
        setPublishTime(setTime ? new Date() : null);
    }

    function handleClose() {
        setHasClicked(false);
        changeEditorState('isPublishing', false);
    }

    function handlePublish() {
        const update = {} as Partial<Post>;
        const variant = {
            language_id: editorState.languageId as number
        } as Partial<PostVariant>;

        if (publishTime) {
            update['published_at'] = dayjs(publishTime).unix()
            variant.status = 'scheduled';
        } else {
            variant.status = 'published';
        }

        update.variants = [variant];

        setHasClicked(true)
        forceSavePost({update, onSave: (post) => {
            toast.success(
                !publishTime ?
                <div>Post Published. <a
                    className="link"
                    href={post.variants[editorState.languageId].url}
                    target="_blank"
                >View</a></div> :
                "Post scheduled"
            , {autoClose: 5000});
        }});
    }

    useEffect(() => {
        if (hasClicked && forceSavePostAjax.status === 'success') {
            handleClose();
        }
    }, [forceSavePostAjax.status])

    useEffect(() => {
        if (editorState.isPublishing) {
            outsideClickListenerRemover = onOutsideClick(publisherViewRef.current, () => changeEditorState('isPublishing', false));
        } else {
            outsideClickListenerRemover && outsideClickListenerRemover()
        }
    }, [editorState.isPublishing])
    
    return <div className={"post-publisher " + (editorState.isPublishing ? "active" : "inactive")}>

        <div
            ref={publisherViewRef}
            className="post-publisher-view" >
            <div className="publisher-head">Publish Post</div>
            <div className="publisher-select-time">
                <Radio
                    name="publish-time"
                    placeholder="Publish Now" 
                    value="now" 
                    onChange={() => handlePublishTimeChange(false)}
                    checkFor={publishTime === null ? "now" : "later"}
                />
                <Radio
                    name="publish-time"
                    placeholder="Publish Later" 
                    value="later" 
                    onChange={() => handlePublishTimeChange(true)}
                    checkFor={publishTime === null ? "now" : "later"}
                />
            </div>
            {
                publishTime !== null ?
                <div className="publish-time-selector">
                    <div className="publish-time-title">Set publish time</div>
                    <div>
                        <DatePicker
                            selected={publishTime}
                            onChange={(date) => setPublishTime(date)}
                            showTimeInput
                            dateFormat="yyyy-MM-dd h:mm aa"
                            minDate={new Date()}
                        />
                    </div>
                </div>
                : null
            }
            <div className="publisher-buttons">
                <button onClick={handleClose} className="button medium secondary">Cancel</button>
                {
                    !publishTime ?
                    <ActionButton 
                        className="medium"
                        status={!hasClicked ? "stale" : (forceSavePostAjax.status || 'stale')}
                        staleName="Publish"
                        loadingName="Publishing" 
                        successName="Published"
                        errorName="Try again"
                        staleOnClick={handlePublish}
                        errorOnClick={handlePublish}
                    /> :
                    <ActionButton
                        className="medium"
                        status={!hasClicked ? "stale" : (forceSavePostAjax.status || 'stale')}
                        staleName="Schedule"
                        loadingName="Scheduling" 
                        successName="Scheduled"
                        errorName="Try again"
                        staleOnClick={handlePublish}
                        errorOnClick={handlePublish}
                    />
                }
            </div>
        </div>

    </div>

}