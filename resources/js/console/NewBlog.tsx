import axios from 'axios';
import {useActions, useValues} from 'kea';
import React, {useEffect, useRef, useState} from 'react'
import {CaretLeftFill, ExclamationCircle} from 'react-bootstrap-icons';
import {getUserEndpoint} from './lib/api';
import blogsLogic from './logic/blogsLogic';
import ActionButton from './ReusableComponents/ActionButton';
import Input from './ReusableComponents/Input'
import {Popup, PopupBodyDefault, PopupHeaderDefault} from './ReusableComponents/Popup'
import Toast from './ReusableComponents/Toast';
import {router} from 'kea-router'
import Callout, {CalloutColors} from "./ReusableComponents/Callout";


export default function NewBlog({ type }: { type: string | null }) {

    const isDev : boolean = type === 'dev';

    const [subdomain, setSubdomain] = useState<string>('');
    const [subdomainError, setSubdomainError] = useState<string|null>(null);

    const [subdomainEdited, setSubdomainEdited] = useState<boolean>(false);

    // TODO: Remove any after ajax type hinting
    const { blogs, createBlogAjax } : any = useValues(blogsLogic)
    const { createBlog } : any = useActions(blogsLogic)

    const { push } = useActions(router);

    const [name, setName] = useState<string>('');
    const [nameError, setNameError] = useState<string|null>(null)

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
        ).catch(() => setSubdomainError("Subdomain already taken"));

    }

    function handleNameChange(val: string) {
        setName(val);
        setNameError(null);

        if (!subdomainEdited) {
            const subdomain = val
                .toLowerCase()
                .replace(/[^a-z0-9-]/g, '-')
                .replace(/-+/g, '-')
                .replace(/(^-|-$)/g, '');
            setSubdomain(subdomain);
            checkSubdomainValue(subdomain);
        }
    }

    function handleSubdomainChange(val: string) {
        setSubdomainEdited(true);

        val = val.toLowerCase();
        setSubdomain(val);
        checkSubdomainValue(val)
    }

    function checkSubdomainValue(val: string) {
        const allowedRegex = /[^a-z0-9-]/;

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
        push('/console');
    }

    function handleCreate() {

        if (name.trim() === "") {
            return setNameError("Name cannot be empty");
        }
        if (subdomain.trim() === "") {
            return setSubdomainError("Subdomain cannot be empty");
        }

        setIsCreating(true);
        createBlog({name, subdomain, isDev});
    }

    return <div className="new-blog-scene">
        <Popup 
            header={<PopupHeaderDefault title={ isDev ? "Create Dev Blog" : "Start a new blog"} />}
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

                    {
                        isDev &&
                        <Callout
                            icon={<ExclamationCircle />}
                            color={CalloutColors.ORANGE}
                            title="Development Blog"
                            text={
                                <div>You are creating a development blog, which can only be used for theme development. Click <a
                                    className="link"
                                    onClick={() => push('/console/new')}
                                >here</a> to create a normal blog.</div>
                            }
                        />
                    }

                    <Input 
                        title="Blog Name"
                        type="text"
                        name="blog-name"
                        autoComplete="off"
                        value={name}
                        error={nameError}
                        onChange={handleNameChange}
                        maxLength={50}
                    />

                    {
                        !isDev &&
                        <Input
                            title={<div>
                                <div>Subdomain</div>
                                <div className="subdomain-rules">Only a-z, 0-9, and hyphens (-)</div>
                            </div>}
                            type="text"
                            name="blog-subdomain"
                            autoComplete="off"
                            value={subdomain}
                            onChange={handleSubdomainChange}
                            error={subdomainError}
                            bottom={
                                <div className="your-blog"><b>{subdomain}.hyvorblogs.io</b></div>
                            }
                            maxLength={35}
                        />
                    }

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