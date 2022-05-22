import React, { useState, useEffect, useRef} from 'react';
import { useActions, useValues } from 'kea';
import DualSetting from '../../ReusableComponents/DualSetting';
import Input from '../../ReusableComponents/Input';
import { Trash, Upload } from 'react-bootstrap-icons';
import SettingsSave from '../../ReusableComponents/SettingsSave';

import subdomainLogic from '../../logic/subdomainLogic';
import blogLogic from '../../logic/blogLogic';
import blogsLogic from '../../logic/blogsLogic';
import languagesLogic from '../../logic/languagesLogic';
import GeneralLanguageSelector from './GeneralLanguageSelector';

import toast from '../../ReusableComponents/Toast';
import axios from 'axios';
import { getUserEndpoint } from '../../lib/api';


let uploadInput = null;
let uploadIconInput = null;


// Should find a way to set up the should save section in the pop-up.
export default function SettingsGeneral() {

    const subdomain = subdomainLogic.values.subdomain;
    const blogLogicBuilt = blogLogic({subdomain})
    const { blog } = useValues(blogLogicBuilt)
    const { updateBlogValue } = useActions(blogLogicBuilt)

    const { languages, getLanguageById } = useValues(languagesLogic({subdomain}))
    const { findBlogBySubdomain } = useValues(blogsLogic)
    const [currentLanguageId, setCurrentLanguageId] = useState( findBlogBySubdomain(subdomain).blog.default_language.id );
    const currentLanguage = getLanguageById(currentLanguageId);
    const variants = blog.variants || [];
    const variant = variants[currentLanguageId] || {};

    const [pointerEvent, setPointerEvent] = useState();
    const [subdomainError, setSubdomainError] = useState(null);
    const [subdomainEdited, setSubdomainEdited] = useState(false)
    const abortControllerRef = useRef(null);

    console.log(blog)


    const [icon, setIcon] = useState();
    const [featuredImage, setFeaturedImage] = useState();

    const [subdomainEdit, setSubdomainEdit] = useState();
    const [facebook, setFacebook] = useState();
    const [twitter, setTwitter] = useState();
    const [linkedIn, setLinkedin] = useState();
    const [youtube, setYoutube] = useState();
    const [instagram, setInstagram] = useState();
    const [github, setGithub] = useState();

    const [variantName, setName] = useState();
    const [variantDescription, setDescription] = useState();

    useEffect(() => {
        setFeaturedImage(blog.featured_image_url)
        setIcon(blog.icon_url)

        setSubdomainEdit(blog.subdomain)
        setFacebook(blog.social_facebook)
        setTwitter(blog.social_twitter)
        setLinkedin(blog.social_linkedin)
        setYoutube(blog.social_youtube)
        setInstagram(blog.social_instagram)
        setGithub(blog.social_github)
    },[blog])

    useEffect(() => {
        setName(variant.name)
        setDescription(variant.description)
    },[variant])

    // subdomain error Handling
    useEffect(() => {
        if (subdomainEdit === "") {
            setSubdomainError(null);
        } else {
            abortControllerRef.current && abortControllerRef.current.abort();
            checkSubdomain();
        }
    }, [subdomainEdit])

    // TO-DO this function should be checked. 
    function checkSubdomain() {
        abortControllerRef.current = new AbortController();

        return axios.get(
            getUserEndpoint('/blog/check-subdomain'),
            {
                signal: abortControllerRef.current.signal,
                params: {
                    subdomainEdit
                }
            }
        ).then(({data: isAvailable}) => {
            if (!isAvailable)
                setSubdomainError("Subdomain already taken");
        }).catch(() => {})
    }

    function handleSubdomainChange(val) {
        setSubdomainEdited(true);

        val = val.toLowerCase();
        setSubdomainEdit(val);

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


    // FeatureImage and Icon section
    function handleFeatureImage() { 
        if (!uploadInput) {
            uploadInput = document.createElement('input')
            uploadInput.type = "file";
            uploadInput.hidden = true;
            document.body.appendChild(uploadInput);
            uploadInput.click();
            uploadInput.onchange = function(e) {
                const files = e.target.files;
                if (!files.length) return;
                if (files.length > 1) {
                    return toast.error("Only one file allowed");
                }
                uploadFeatureImage({featureImage: files[0]});
            }
        } else {
            uploadInput.click();
        }
    }

    function handleIcon() { 
        if (!uploadIconInput) {
            uploadIconInput = document.createElement('input')
            uploadIconInput.type = "file";
            uploadIconInput.hidden = true;
            document.body.appendChild(uploadIconInput);
            uploadIconInput.click();
            uploadIconInput.onchange = function(e) {
                const files = e.target.files;
                if (!files.length) return;
                if (files.length > 1) {
                    return toast.error("Only one file allowed");
                }
                uploadIcon({icon: files[0]});
            }
        } else {
            uploadIconInput.click();
        }
    }

    function handleSave(e) {
        e.preventDefault();
        if (subdomainEdit.trim() === "") {
            return setSubdomainError("Subdomain cannot be empty");
        }
        updateData({
            subdomainEdit:subdomainEdit,
            name: variantName,
            description:variantDescription,
            social_facebook:facebook,
            social_twitter:twitter,
            social_linkedin:linkedIn,
            social_youtube: youtube,
            social_instagram:instagram,
            social_github:github
        });
    }

    function shouldSave() {
        // console.log('should save')
        // const shouldSave
        // if(subdomain){
            //  return subdomainEdit !== ""
        // }
        // subdomain !== ""
        // name !== ""
        // description !== ""
        // icon !== ""
        // featuredImage !== ""
        // facebook !== ""
        // twitter !== ""
        // linkedIn !== ""
        // youtube !== ""
        // instagram !== ""
        // github !== ""
    }

    function handleDiscard() {
    //     setSubdomainEdit('');
    //     setName('');
    //     setDescription('');
    //     setIcon('');
    //     setFeaturedImage('');
    //     setFacebook('');
    //     setTwitter('');
    //     setLinkedin('');
    //     setYoutube('');
    //     setInstagram('');
    //     setGithub('');

    }

    return <div className="settings-general">

        <div className="title">
            General Settings
        </div>
        <GeneralLanguageSelector 
            subdomain={subdomain}
            languages={languages} 
            currentLanguageId={currentLanguageId}
            onChange={setCurrentLanguageId}
        />

        <DualSetting 
            title="Subdomain" 
            description="Subdomain is used to uniquely identify your blog within Hyvor Blogs"
            right={
                <div>
                     <Input 
                        title={null}
                        type="text"
                        name="subdomainCheck"
                        id="subdomainCheck"
                        // value={blog.subdomain}
                        value = {subdomainEdit} 
                        // onChange={setSubdomainEdit}
                        onChange={handleSubdomainChange}
                        error={subdomainError}
                        // onChange={handleSubdomain}
                    />
                </div>
            }
        />
        <DualSetting 
            title="Name"
            description="Name of your blog"
            right={
                <Input 
                    title={null}
                    type="text"
                    name="name"
                    value={variantName}
                    onChange={setName}
                />
            }
        />
        <DualSetting 
            title="Description"
            description="A short description (or sub-title) for your blog"
            right={
                <Input 
                    title={null}
                    type="text"
                    name="description" 
                    value={variantDescription}
                    onChange={setDescription}
                />
            }
        />

        <DualSetting 
            title="Icon"
            description="The icon of your blog"
            right={
                // <Input 
                //     title={null}
                //     type="text"
                //     name="icon"
                //     value={icon}
                //     onChange={setIcon}
                // />
                // <div>
                <div class="image-head">
                    {/* <img src="https://picsum.photos/200/200" alt="Avatar" className="image-center"/> */}
                    <img src={icon} alt="Avatar" className="image-center"/>
                    <button 
                        onClick={uploadIconAjax.status === 'loading' ? null: handleIcon}
                        className="button-style uploadButton button small inactive">
                        {uploadIconAjax.status === 'loading' ? "Uploading..." : 
                            <span>Upload <Upload /></span>
                        }
                    </button>
                </div>
            }
        />

        <DualSetting 
            title="Featured Image"
            description="A featured image for the homepage of your blog. Useful when sharing on social media"
            right={
                // <Input 
                //     title={null}
                //     type="text"
                //     name="featured Image"
                //     value={featuredImage}
                //     onChange={setFeaturedImage}
                // />

                <div class="image-head">
                    <img src={featuredImage} alt="Avatar" className="image-center"/>
                    <button 
                        onClick={uploadFeatureImageAjax.status === 'loading' ? null: handleFeatureImage}
                        className="button-style uploadButton button small inactive">
                            {   
                                uploadFeatureImageAjax.status === 'loading' ? "Uploading..." : 
                                    <span>Upload <Upload /></span> 
                            }
                    </button>
                </div>
            }
        />

        <DualSetting 
            title="Social Media"
            description="Links to your social media channels (use full URLs with https://)."
            right={null}
        />

        <div className="swift-settings social-media">
            <DualSetting 
                title="Facebook"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="facebook"
                        value={facebook}
                        onChange={setFacebook}
                    />
                }
            />
            <DualSetting 
                title="Twitter"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="twitter"
                        value={twitter}
                        onChange={setTwitter}
                    />
                }
            />
            <DualSetting 
                title="Linkedin"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="linkedIn"
                        value={linkedIn}
                        onChange={setLinkedin}
                    />
                }
            />
            <DualSetting 
                title="Youtube"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="youtube"
                        value={youtube}
                        onChange={setYoutube}
                    />
                }
            />
            <DualSetting 
                title="Instagram"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="instagram"
                        value={instagram}
                        onChange={setInstagram}
                    />
                }
            />
            <DualSetting 
                title="Github"
                right={
                    <Input 
                        title={null}
                        type="text"
                        name="github"
                        value={github}
                        onChange={setGithub}
                    />
                }
            />
        </div>

        <SettingsSave 
            should={shouldSave}
            onSave={handleSave}
            onDiscard={handleDiscard}
        />

    </div>

}