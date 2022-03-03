import React, { useEffect, useState } from 'react'
import Radio from '../ReusableComponents/Radio'
import DatePicker from 'react-datepicker';
import { usePostActions, usePostValues } from './usePost';
import dayjs from 'dayjs';
import ActionButton from '../ReusableComponents/ActionButton';
import { toast } from 'react-toastify';

export default function PostPublisher({id, publisherViewRef, isOpen, closePublisher}) {

    const { savePostAjax  } = usePostValues(id);
    const { savePost, updatePostValue } = usePostActions(id);

    const [hasClicked, setHasClicked] = useState(false)
    const [publishTime, setPublishTime] = useState(null)

    function handlePublishTimeChange(setTime) {
        setPublishTime(setTime ? new Date() : null);
    }

    function handleClose() {
        setHasClicked(false);
        closePublisher();
    }

    function handlePublish() {
        const update = {};
        if (publishTime) {
            update['published_at'] = dayjs(publishTime).unix()
            update['status'] = 'scheduled';
        } else {
            update['status'] = 'published';
        }
        setHasClicked(true)
        savePost({update, onSave: (post) => {
            toast.success(
                !publishTime ?
                <div>Post Published. <a className="link" href={post.url} target="_blank">View</a></div> :
                "Post scheduled"
            , {autoClose: 5000});
        }});
    }

    useEffect(() => {
        if (hasClicked && savePostAjax.status === 'success') {
            handleClose();
        }
    }, [savePostAjax.status])
    
    return <div className={"post-publisher " + (isOpen ? "active" : "inactive")}>

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
                        status={!hasClicked ? "stale" : savePostAjax.status} 
                        staleName="Publish"
                        loadingName="Publishing" 
                        successName="Published"
                        errorName="Try again"
                        staleOnClick={handlePublish} 
                        successOnClick={null}
                        errorOnClick={handlePublish}
                    /> :
                    <ActionButton
                        className="medium"
                        status={!hasClicked ? "stale" : savePostAjax.status} 
                        staleName="Schedule"
                        loadingName="Scheduling" 
                        successName="Scheduled"
                        errorName="Try again"
                        staleOnClick={handlePublish} 
                        successOnClick={null}
                        errorOnClick={handlePublish}
                    />
                }
            </div>
        </div>

    </div>

}