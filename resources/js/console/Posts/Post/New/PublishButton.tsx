import React, { Fragment, useState } from "react";
import { usePostActions, usePostValues } from "../helpers";
import { CheckCircleFill, ExclamationCircle, ExclamationTriangle, ExclamationTriangleFill, SendFill } from "react-bootstrap-icons";
import { Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault } from "../../../ReusableComponents/Popup";
import { bringLeftHeaderToFront } from "./z-index";
import Radio from "../../../ReusableComponents/Radio";
import ReactDatePicker from "react-datepicker";
import ActionButton from "../../../ReusableComponents/ActionButton";
import { Post, PostVariant } from "../../../types";
import dayjs from "dayjs";


export default function PublishButton({id} : {id: number}) {

    const [publisherOpen, setPublisherOpen] = useState(false);

    function handleClick(e: React.MouseEvent<HTMLButtonElement, MouseEvent>) {
        e.stopPropagation();
        setPublisherOpen(true);
        bringLeftHeaderToFront();
    }
 
    return <Fragment>
        <button 
            className="button medium"
            onClick={handleClick}
        >
            Publish <SendFill />
        </button>

        {
            publisherOpen && <PublisherPopup 
                id={id}
                onClose={() => setPublisherOpen(false)}
            />
        }

    </Fragment>

}

function PublisherPopup({id, onClose} : {id: number, onClose: () => void}) {

    const [publishTime, setPublishTime] = useState<Date | null>(null)
    const { currentVariant } = usePostValues(id);
    const { savePostDiff } = usePostActions(id);

    function handlePublishTimeChange(setTime: boolean) {
        setPublishTime(setTime ? new Date() : null);
    }

    function handlePublish() {
        
        const diff = {} as Partial<Post>;
        const variant = {...currentVariant};

        if (publishTime) {
            diff['published_at'] = dayjs(publishTime).unix()
            variant.status = 'scheduled';
        } else {
            variant.status = 'published';
        }

        diff.variants = [variant as PostVariant];

        savePostDiff({diff, onSave: () => {
            onClose();
        }});

    }

    const validation = {
        slug: !!currentVariant.slug,
        title: !!currentVariant.title,
        description: !!currentVariant.description,
    }

    return <div className="post-publisher">
        <Popup 
            isCenter={true}
            body={
                <div className="post-publisher-inner">
                    <div className="publisher-title">
                        Publish Post
                    </div>

                    <Validation validation={validation} />

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
                            placeholder="Schedule for Later" 
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
                                <ReactDatePicker
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
                        <button onClick={onClose} className="button medium secondary">Cancel</button>
                        {
                            !publishTime ?
                            <ActionButton
                                className="medium"
                                status="stale"
                                staleName="Publish"
                                loadingName="Publishing" 
                                successName="Published"
                                errorName="Try again"
                                staleOnClick={handlePublish}
                                errorOnClick={handlePublish}
                            /> :
                            <ActionButton
                                className="medium"
                                status="stale"
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
            }
        />
    </div>

}

function Validation({validation} : {validation: {slug: boolean, title: boolean, description: boolean}}) {

    if (validation.slug && validation.title && validation.description) {
        return null;
    }

    return <div className="publisher-validation">

        {
            Object.entries(validation).map(([key, value]) => {

                if (value) {
                    return null;
                }

                return <div className="publisher-validation-item">
                    <div className="publisher-validation-item-icon">
                        <ExclamationTriangleFill />
                    </div>
                    <div className="publisher-validation-item-text">
                        {
                            key === "slug" ? "Slug is not set, it will be auto-generated" :
                            key === "title" ? "Title is not set" :
                            key === "description" ? "Description is not set" : null
                        }
                    </div>
                </div>

            })
        }

    </div>

}