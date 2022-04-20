import React, {useState, useEffect} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, BoxArrowInRight, Link} from 'react-bootstrap-icons';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import ProfileImage from '../../ReusableComponents/ProfileImage';
import ReactSelect, { components } from 'react-select';
import DualSetting from '../../ReusableComponents/DualSetting';
import Input from '../../ReusableComponents/Input';
import languagesLogic from '../../logic/languagesLogic';
import blogsLogic from '../../logic/blogsLogic';
import UserLanguageSelector from './UserLanguageSelector';
import usersLogic from '../../logic/usersLogic';

import {toast} from 'react-toastify'
import subdomainLogic from '../../logic/subdomainLogic';
import Loader from '../../ReusableComponents/Loader';
import Toast from '../../ReusableComponents/Toast';
import NoResults from '../../ReusableComponents/NoResults'; 

let uploadInput = null;

export default function Users({user, subdomain}) {

    const usersLogicBuilt = usersLogic({subdomain})
    const { remove , updateData, upload} = useActions(usersLogicBuilt)
    const { updateDataAjax, uploadAjax, picture } = useValues(usersLogicBuilt)

    // Language Section
    const { languages, getLanguageById } = useValues(languagesLogic({subdomain}))
    const { findBlogBySubdomain } = useValues(blogsLogic)
    const [currentLanguageId, setCurrentLanguageId] = useState( findBlogBySubdomain(subdomain).blog.default_language.id );
    const currentLanguage = getLanguageById(currentLanguageId);

    const variants = user.variants || [];
    const variant = variants[currentLanguageId] || {};

    // console.log(variant);
    // console.log(variant.language_id); // this get the data from the user variant database
    // console.log(currentLanguage.is_primary) // this get the data from the language logic (true)
    // console.log(currentLanguageId) // this get the current language Id of an specific user.
    // console.log(currentLanguage.name)

    // To get the data of the current language we will have to include a if statement and then add the language data in to it.
    // And then we can pass the language data accordingly.
    // And also we will have to add a form disable function in it.

    // console.log(user.hyvor_user_id)

    // To disable editing in other languages.
    const [pointerEvent, setPointerEvent] = useState();

    // To display data in the table
    const [getTableName, setTableName] = useState(null);
    useEffect(() => {
        if(currentLanguage.is_primary === true){
            if(currentLanguage.id === variant.language_id){
                if(user.id === variant.user_id){
                    setTableName(variant.name)
                }
            }
        }
    })

    function handleUpload() { 

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
                upload({file: files[0]});
            }
        } else {
            uploadInput.click();
        }

    }

    // To display data in the pop-up
    const [variantName, setVariantName] = useState(null);
    const [variantBio, setVariantBio] = useState(null);
    const [variantLocation, setVariantLocation] = useState(null);

    useEffect(() => {
        if(currentLanguage.is_primary == true)
        {
            setPointerEvent()
            if(currentLanguageId == variant.language_id){
                if(user.id == variant.user_id){
                    setVariantName(variant.name)
                    setVariantBio(variant.bio)
                    setVariantLocation(variant.location)
                }
            }
        }
        // else if(variant.name != null || variant.bio != null || variant.location != null)
        else
        {
            setPointerEvent("pointerEvent")
            if(currentLanguageId == variant.language_id){
                if(user.id == variant.user_id){
                    setVariantName(variant.name)
                    setVariantBio(variant.bio)
                    setVariantLocation(variant.location)
                }
            }
        }
    },[])

    
    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");

    const [updatePopUpOpened, setUpdatePopUpOpened] = useState(false);
    const [deletePopupOpened, setDeletePopupOpened] = useState(false);

    // Get usr type
    const [userType, setUserType] = useState();

    useEffect(() => {
        if(user.hyvor_user_id == null){
            setUserType("guest")
        } else {
            setUserType("hyvor")
        }
    })


    // Select option section.
    const [status, setStatus] = useState({statusType: ""});
    const [role, setRole] = useState({type: ""});

    function handleRole({value}){
        setRole({...role, type: value}) 
    }

    function handleStatus({value}){
        setStatus({...status, statusType: value})
    }
    const selectRole = [
        { label: 'owner', value: 'owner' },
        { label: 'admin', value: 'admin'},
        { label: 'editor', value: 'editor' },
        { label: 'writer', value: 'writer'},
        { label: 'contributor', value: 'contributor' },
        { label: 'finance', value: 'finance'}
    ];  

    const selectStatus = [
        { label: 'invited', value: 'invited' },
        { label: 'active', value: 'active'},
        { label: 'blocked', value: 'blocked' },
    ]; 

    // Update section
    // const [updateTagData, setUpdate] = useState({userId: user.id})
    const [ userSlug, setSlug ] = useState(user.slug);
    const [ userEmail, setEmail ] = useState(user.email);
    const [ userUrl, setUrl ] = useState(user.url);
    
    const [ facebook, setFacebook ] = useState(user.social_facebook);
    const [ twitter, setTwitter ] = useState(user.social_twitter);
    const [ linkedIn, setLinkedIn ] = useState(user.social_linkedin);
    const [ youtube, setYoutube ] = useState(user.social_youtube);
    const [ instagram, setInstagram ] = useState(user.social_instagram);
    
    // function handleVariationName({e}){
    //     setVariantName({value})
    // }

    // const [user, setUser] = useState({name: ''})
    // const handleChange = e => {
    //     setUser({...user, name: e.target.value})
    //  }

     
    function handleUpdate(e) {
        e.preventDefault();
        setStyleUpdateIcon('table-update-popup')
        setUpdatePopUpOpened(true);
    }
    function submitUser (e) {
        e.preventDefault();
        updateData({
            id: user.id,
            role:role.type,
            status:status.statusType,
            slug:userSlug,
            picture:1,
            email:userEmail,
            name: variantName,
            url:userUrl,
            social_facebook:facebook,
            social_twitter:twitter,
            social_linkedin:linkedIn,
            social_youtube: youtube,
            social_instagram:instagram,
            bio:variantBio,
            location:variantLocation,
        });
        setStyleUpdateIcon('table-button')
        setUpdatePopUpOpened(false)
    }
    function handleUpdateCancel(){
        setStyleUpdateIcon('table-button')
        setUpdatePopUpOpened(false)
    }

     // Delete Section
    function handleDelete(e) {
        e.preventDefault();
        setDeletePopupOpened(true);
        setStyleDeleteIcon("table-delete-popup");
    }
    function handleDoDelete() {
        remove({
            id:user.id,
            languageId:currentLanguageId
        });
        setDeletePopupOpened(false);
        setStyleDeleteIcon("table-button");
    }
    function handleDeleteCancel(){
        setStyleDeleteIcon("table-button");
        setDeletePopupOpened(false)
    }

    return <div>
        <div className="global-table-body">
            <div className="table-body-five">
                <div className="table-item"> {getTableName}</div>
                <div className="table-item"> {user.slug} </div>
                <div className="table-item"> {user.email} </div>
                <div className="table-item"> 
                    <div className="user-role">{user.role}</div>
                </div>
                <div className="table-actions">
                    <div className="table-edit" >
                        <span className={styleUpdateIcon} onClick={handleUpdate}>
                            <PencilFill size={10} />
                        </span>

                        {
                            updatePopUpOpened ?
                                // userType == 'guest' ?
                                <div className="popup-width">
                                    <Popup
                                        header={
                                            <div>
                                                <PopupHeaderDefault title='Update User' />
                                                <UserLanguageSelector 
                                                    id={user.id} 
                                                    subdomain={subdomain}
                                                    languages={languages} 
                                                    variants={variants} 
                                                    currentLanguageId={currentLanguageId}
                                                    onChange={setCurrentLanguageId}
                                                />
                                            </div>
                                        }
                                        body={
                                            <PopupBodyDefault>
                                                <div>
                                                    {/* <ProfileImage/> */}
                                                    <div className="global-avatar">
                                                        <div class="avatar">
                                                        {/* <input type="file" id="actual-btn" hidden/> */}
                                                        <img src={user.picture_url} alt="Avatar" className="avatar-center"/>
                                                    </div>
                                                    <input type="file" id="actual-btn" hidden/>
                                                    
                                                    <div for="actual-btn" className="upload-lable"
                                                        onClick={uploadAjax.status === 'loading' ? null: handleUpload}>
                                                            {uploadAjax.status === 'loading' ? "Uploading..." : <span> Choose File</span>}
                                                    </div>
                                                </div>

                                                    <Input 
                                                        title="Name"
                                                        type="text"
                                                        name="name"
                                                        value={variantName}
                                                        onChange={setVariantName}
                                                        placeholder="Name"
                                                    />
                                                    <div className={pointerEvent}>
                                                        <Input 
                                                            title="Slug"
                                                            type="text"
                                                            name="slug"
                                                            value={userSlug}
                                                            onChange={setSlug}
                                                            placeholder="Slug"
                                                        />
                                                    </div>
                                                    <div className={pointerEvent}>
                                                        <DualSetting 
                                                            left={
                                                                <Input 
                                                                    title="Email"
                                                                    type="text"
                                                                    name="email"
                                                                    value={userEmail}
                                                                    onChange={setEmail}
                                                                    placeholder="Email"
                                                                />
                                                            }
                                                            right={
                                                                <Input 
                                                                    title="Url"
                                                                    type="text"
                                                                    name="url"
                                                                    value={userUrl}
                                                                    onChange={setUrl}
                                                                    placeholder="Url"
                                                                />
                                                            }
                                                        />
                                                    </div>
                                                    <div className={pointerEvent}>
                                                        <DualSetting 
                                                            left={
                                                                <div>
                                                                    <div className="popup-type-margin">Role</div>
                                                                    <SelectUserRole  options = {selectRole} onChange = {handleRole}/>
                                                                </div>
                                                            }
                                                            right={
                                                                <div>
                                                                    <div className="popup-type-margin">Status</div>
                                                                    <SelectUserRole  options = {selectStatus} onChange = {handleStatus}/>
                                                                </div>
                                                                }
                                                        />
                                                    </div>
                                                    
                                                     <DualSetting 
                                                        left={
                                                            <Input 
                                                                title="Location"
                                                                type="text"
                                                                name="location"
                                                                value={variantLocation}
                                                                onChange={setVariantLocation}
                                                                placeholder="Location"
                                                            />
                                                        }
                                                        right={
                                                            <div className={pointerEvent}>
                                                                <Input 
                                                                    title="Facebook"
                                                                    type="text"
                                                                    name="facebook"
                                                                    value={facebook}
                                                                    onChange={setFacebook}
                                                                    placeholder="Facebook"
                                                                />
                                                            </div>
                                                        }
                                                    />
                                                    <div className={pointerEvent}>
                                                        <DualSetting 
                                                            left={
                                                                <Input 
                                                                    title="Twitter"
                                                                    type="text"
                                                                    name="twitter"
                                                                    value={twitter}
                                                                    onChange={setTwitter}
                                                                    placeholder="Twitter"
                                                                />
                                                            }
                                                            right={
                                                                <Input 
                                                                    title="LinkedIn"
                                                                    type="text"
                                                                    name="LinkedIn"
                                                                    value={linkedIn}
                                                                    onChange={setLinkedIn}
                                                                    placeholder="linkedIn"
                                                                />
                                                            }
                                                        />
                                                        <DualSetting 
                                                            left={                                                                  
                                                                <Input 
                                                                    title="Youtube"
                                                                    type="text"
                                                                    name="youtube"
                                                                    value={youtube}
                                                                    onChange={setYoutube}
                                                                    placeholder="Youtube"
                                                                />
                                                            }
                                                            right={                                                                    
                                                                <Input 
                                                                    title="Instagram"
                                                                    type="text"
                                                                    name="instagram"
                                                                    value={instagram}
                                                                    onChange={setInstagram}
                                                                    placeholder="Instagram"
                                                                /> 
                                                            }
                                                        />
                                                    </div>

                                                    <div className="popup-type-margin">Bio</div>
                                                    <textarea 
                                                        className="input"
                                                        placeholder="Write a bio..."
                                                        value={variantBio}
                                                        onChange={setVariantBio}
                                                        maxLength={350}
                                                    ></textarea>

                                                    <div className="table-delete">
                                                        <span className = 'button danger' onClick={handleDelete}>
                                                            Delete
                                                        </span>
                                                        {
                                                            deletePopupOpened ?
                                                            <PopupConfirm
                                                                title="Delete Permanently"
                                                                text="Are you sure to delete this user permanently? You will not be able to access it anymore."
                                                                name="Delete"
                                                                buttonClass="danger"
                                                                onClick={handleDoDelete}
                                                                onCancel={handleDeleteCancel}
                                                            />
                                                            : null
                                                        }
                                                    </div> 
                                                </div>
                                            </PopupBodyDefault>
                                        }
                                        footer={
                                            <PopupFooterDoubleButton
                                                onCancel={handleUpdateCancel}
                                                onClick={(e)=> {submitUser(e)}}
                                                name='Update'
                                            />
                                        }
                                    /> 
                                </div>
                            : null
                        }
                    </div>
                    <div className="table-view">
                        <span className='table-button'>
                            <Link size={10} />
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
}

function SelectUserRole({options, onChange, defaultValue}) {
    
    const customStyles = {

        control: (provided) => ({
            ...provided,
            width: '100%',
            fontSize: '12px',
            // padding: '3px 5px',
            borderRadius: '20px',
            border: 'none',
            background: '#f5f5f5',
            fontFamily: 'inherit',
            transition:' 0.3s box-shadow',
            alignItems: 'center',
            height: '20px',
            overflowX: 'auto',
        }),

        valueContainer: (base, state) => ({
            ...base,
            fontFamily: 'Helvetica, sans-serif !important',
            fontSize: 12,
            fontWeight: 500,
            color: '#000',
            paddingLeft: '15px',
            paddingRight: '15px',
            display: 'flex',
            // paddingTop: '-20px',
            // paddingBottom: '-20px',

        }),

        option: (provided, state) => ({
            ...provided,
            color: '#000',
            backgroundColor: state.isSelected ? '#f1e8e8' : '#fff',
            width: '95%',
            display: 'flex',
            minHeight: 'initial',
            borderRadius: '20px',
            border: 'none',
            transition: '0.3s box-shadow',
            margin:'10px',
            '&:hover': {
                backgroundColor: '#f1e8e8',
            },
        }),

        singleValue: (provided, state) => {
            const opacity = state.isDisabled ? 0.5 : 1;
            const transition = 'opacity 300ms';
        
            return { ...provided, opacity, transition };
        },

        dropdownIndicator: (base) => ({
            ...base,
            display:'none',
        }),

        clearIndicator: (base) => ({
            ...base,
            display:'none',
        }),

        indicatorSeparator:(base)=>({
            ...base,
            display:'none',
          }),
      }

    function handleTag(data){
        
        const lastValue = data[data.length - 1];
        const userId = lastValue.value
    }
    
    const SelectUserRole = () => (
        <ReactSelect
            defaultValue={defaultValue}
            styles={customStyles}
            options={options}
            maxMenuHeight={150}
            // onChange={() => {}}
            onChange={onChange}
            // isMulti 
        />
    );

    return <SelectUserRole/>

}