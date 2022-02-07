import { useActions, useValues } from 'kea';
import React, { useState } from 'react'
import { CaretLeftFill } from 'react-bootstrap-icons';
import blogsLogic from './logic/blogsLogic';
import subdomainLogic from './logic/subdomainLogic';
import Input from './ReusableComponents/Input'
import { Popup, PopupBodyDefault, PopupFooterSingleButton, PopupHeaderDefault } from './ReusableComponents/Popup'


export default function NewBlog() {

    const [subdomain, setSubdomain] = useState('');
    const [subdomainError, setSubdomainError] = useState(null);
    const [subdomainSuccess, setSubdomainSuccess] = useState(null);

    const { blogs } = useValues(blogsLogic)
    const { setSubdomain: setSubdomainInLogic } = useActions(subdomainLogic)

    const [name, setName] = useState('');

    function onNameChange(val) {
        setName(val);

        var subdomain = val.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/(^-|-$)/g, '');
        setSubdomain(subdomain);
    }

    function onSubdomainChange(val) {
        val = val.toLowerCase();
        setSubdomain(val);

        var allowedRegex = /[^a-z0-9-]/;

        if (val.substr(0, 1) === '-') {
            setSubdomainError('Cannot start with -');
        } else if (val.substr(val.length - 1) === '-') {
            setSubdomainError('Cannot end with -');
        } else if (val.match(allowedRegex)) {
            const firstLetter = val.match(allowedRegex)[0]
            setSubdomainError('Cannot contain ' + firstLetter);
        } else {
            setSubdomainError(null)
        }
    }

    function handleBack() {
        setSubdomainInLogic(blogs[0].blog.subdomain, null, true);
    }

    return <div className="new-blog-scene">
        <Popup 
            header={<PopupHeaderDefault title="Start a new blog" />}
            body={<PopupBodyDefault>
                <div className="onboarding-body">

                    {
                        blogs.length ?
                        <div className="back-button">
                            <button 
                                className="button text-only"
                                onClick={handleBack}
                            >
                                <CaretLeftFill /> <span>Back</span>
                            </button>
                        </div> : null
                    }

                    <Input 
                        title="Blog Name"
                        type="text"
                        name="blog-name"
                        autocomplete={false}
                        value={name}
                        onChange={onNameChange}
                        maxLength={50}
                    />
                    <Input 
                        title={<div>
                            <div>Subdomain</div>
                            <div className="subdomain-rules">Only a-z, 0-9, and hyphens (-)</div>
                        </div>}
                        type="text"
                        name="blog-subdomain"
                        autocomplete={false}
                        value={subdomain}
                        onChange={onSubdomainChange}
                        error={subdomainError}
                        bottom={
                            <div className="your-blog"><b>{subdomain}.hyvorblogs.io</b></div>
                        }
                        maxLength={35}
                    />
                </div>
            </PopupBodyDefault>}
            footer={<PopupFooterSingleButton name="Continue" onClick={() => {}} />}
        />
    </div>

}