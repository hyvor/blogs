import axios from 'axios';
import { useActions, useValues } from 'kea';
import React, { useEffect, useRef, useState } from 'react'
import { CaretLeftFill } from 'react-bootstrap-icons';
import { getUserEndpoint } from './lib/api';
import blogsLogic from './logic/blogsLogic';
import subdomainLogic from './logic/subdomainLogic';
import ActionButton from './ReusableComponents/ActionButton';
import Input from './ReusableComponents/Input'
import { Popup, PopupBodyDefault, PopupFooterSingleButton, PopupHeaderDefault } from './ReusableComponents/Popup'
import Toast from './ReusableComponents/Toast';


export default function NewBlog() {

    const [subdomain, setSubdomain] = useState('');
    const [subdomainError, setSubdomainError] = useState(null);

    const [subdomainEdited, setSubdomainEdited] = useState(false)

    const { blogs, createBlogAjax } = useValues(blogsLogic)
    const { createBlog } = useActions(blogsLogic)
    const { setSubdomain: setSubdomainInLogic } = useActions(subdomainLogic)

    const [name, setName] = useState('');
    const [nameError, setNameError] = useState(null)

    const abortControllerRef = useRef(null);

    const [isCreating, setIsCreating] = useState(false);

    useEffect(() => {
        if (subdomain === "") {
            setSubdomainError(null);
        } else {
            abortControllerRef.current && abortControllerRef.current.abort();
            checkSubdomain();
        }
    }, [subdomain])

    function checkSubdomain() {
        abortControllerRef.current = new AbortController();

        return axios.get(
            getUserEndpoint('/blog/check-subdomain'),
            {
                signal: abortControllerRef.current.signal,
                params: {
                    subdomain
                }
            }
        ).then(({data: isAvailable}) => {
            if (!isAvailable)
                setSubdomainError("Subdomain already taken");
        }).catch(() => {})

    }

    function handleNameChange(val) {
        setName(val);
        setNameError(null);

        if (!subdomainEdited) {
            var subdomain = val.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/(^-|-$)/g, '');
            setSubdomain(subdomain);
        }
    }

    function handleSubdomainChange(val) {
        setSubdomainEdited(true);

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

    function handleCreate() {

        if (name.trim() === "") {
            return setNameError("Name cannot be empty");
        }
        if (subdomain.trim() === "") {
            return setSubdomainError("Subdomain cannot be empty");
        }

        setIsCreating(true);
        createBlog({name, subdomain});
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
                        error={nameError}
                        onChange={handleNameChange}
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
                        onChange={handleSubdomainChange}
                        error={subdomainError}
                        bottom={
                            <div className="your-blog"><b>{subdomain}.hyvorblogs.io</b></div>
                        }
                        maxLength={35}
                    />
                </div>
            </PopupBodyDefault>}
            footer={
                <div className="popup-footer-single">
                    <ActionButton
                        status={!isCreating ?  "stale" : createBlogAjax.status} 
                        staleName="Create"
                        loadingName="Creating"
                        successName="Created"
                        errorName="Try again"
                        staleOnClick={handleCreate}
                        errorOnClick={handleCreate}
                    />
                </div>
            }
        />
        {
            createBlogAjax.status === 'error' ?
                <Toast
                    text={createBlogAjax.error}
                    type="error"
                /> 
            : null
        }
    </div>

}