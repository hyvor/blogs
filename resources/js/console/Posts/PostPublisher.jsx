import React, { useState } from 'react'
import Radio from '../ReusableComponents/Radio'
import DatePicker from 'react-datepicker';

export default function PostPublisher({id, publisherViewRef, isOpen, closePublisher}) {

    const [publishTime, setPublishTime] = useState(null)

    function handlePublishTimeChange(setTime) {
        setPublishTime(setTime ? new Date() : null);
    }

    function handlePublish() {
        
    }
    
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
                    <div className="publish-time-title">Time to publish</div>
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
                <button onClick={closePublisher} className="button secondary">Cancel</button>
                <button onClick={handlePublish} className="button">Publish</button>
            </div>
        </div>

    </div>

}