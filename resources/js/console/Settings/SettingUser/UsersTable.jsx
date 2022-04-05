import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, BoxArrowInRight} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import subdomainLogic from '../../logic/subdomainLogic';
import usersLogic from '../../logic/usersLogic';
import Loader from '../../ReusableComponents/Loader';
import Toast from '../../ReusableComponents/Toast';
import NoResults from '../../ReusableComponents/NoResults';
import Input from '../../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import ProfileImage from '../../ReusableComponents/ProfileImage';
import ReactSelect, { components } from 'react-select';
import DualSetting from '../../ReusableComponents/DualSetting';



export default function Users(props) {

    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");

    const [updatePopUpOpened, setUpdatePopUpOpened] = useState(false);
    const [deletePopupOpened, setDeletePopupOpened] = useState(false);

    // Get usr type
    const [userType, setUserType] = useState("guest");


    const selectOptions = [
        { label: 'Permanent', value: '301' },
        { label: 'Temporary', value: '302'}
    ];  

    // Update section
    function handleUpdate(e) {
        e.preventDefault();
        setStyleUpdateIcon('table-update-popup')
        setUpdatePopUpOpened(true);
    }
    function submitUser (e) {
        e.preventDefault();
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
        console.log(id);
        // toast("File deleted", {autoClose: 1500});
        // remove({id});
        setDeletePopupOpened(false);
        setStyleDeleteIcon("table-button");
    }
    function handleDeleteCancel(){
        setStyleDeleteIcon("table-button");
        setDeletePopupOpened(false)
    }

    // If the user is an hyvor userType
    const [isChecked, setIsChecked] = useState(0);
    const [checkboxEvent, setCheckBoxEvent] = useState('pointerEvent');

    function onCheckChange(){
        setIsChecked(1);

        if(isChecked == 0){
            setCheckBoxEvent()
        }
    
        if(isChecked == 1){
            setCheckBoxEvent('pointerEvent');
        }
    }

    return <div>
        <div className="global-table-body">
            <div className="table-body-five">
                <div className="table-item"> Rasif</div>
                <div className="table-item"> rasif-sahl </div>
                <div className="table-item"> Sahl </div>
                <div className="table-item"> 
                    <div className="user-role">Admin</div>
                </div>
                <div className="table-actions">
                    <div className="table-edit" >
                        <span className={styleUpdateIcon} onClick={handleUpdate}>
                            <PencilFill size={10} />
                        </span>

                        {
                            updatePopUpOpened ?

                                userType == 'hyvor' ?

                                    <div className="popup-width">
                                        <Popup
                                            body={
                                                <PopupBodyDefault>
                                                    <div>
                                                        <ProfileImage/>

                                                        <Input 
                                                            title="Name"
                                                            type="text"
                                                            name="name"
                                                            // value={name}
                                                            // onChange={setName}
                                                            placeholder="Name"
                                                        />
                                                        <Input 
                                                            title="Slug"
                                                            type="text"
                                                            name="slug"
                                                            // value={slug}
                                                            // onChange={setSlug}
                                                            placeholder="Slug"
                                                        />

                                                        <DualSetting 
                                                            left={
                                                                <Input 
                                                                    title="Email"
                                                                    type="text"
                                                                    name="email"
                                                                    // value={description}
                                                                    // onChange={setDescription}
                                                                    placeholder="Description"
                                                                />
                                                            }
                                                            right={
                                                                <div>
                                                                    <div className="popup-type-margin">Role</div>
                                                                    <SelectUserRole  options = {selectOptions}/>
                                                                </div>
                                                            }
                                                        />

                                                        <DualSetting 
                                                            left={
                                                                <Input 
                                                                    title="Url"
                                                                    type="text"
                                                                    name="url"
                                                                    // value={url}
                                                                    // onChange={setUrl}
                                                                    placeholder="Url"
                                                                />
                                                            }
                                                            right={
                                                                <Input 
                                                                title="Location"
                                                                type="text"
                                                                name="location"
                                                                // value={location}
                                                                // onChange={setLocation}
                                                                placeholder="Location"
                                                            />
                                                            }
                                                        />

                                                        <DualSetting 
                                                            left={
                                                                <Input 
                                                                    title="Facebook"
                                                                    type="text"
                                                                    name="facebook"
                                                                    // value={facebook}
                                                                    // onChange={setFacebook}
                                                                    placeholder="Facebook"
                                                                />
                                                            }
                                                            right={
                                                                <Input 
                                                                title="Twitter"
                                                                type="text"
                                                                name="twitter"
                                                                // value={twitter}
                                                                // onChange={setTwitter}
                                                                placeholder="Twitter"
                                                            />
                                                            }
                                                        />

                                                        <DualSetting 
                                                            left={
                                                                <Input 
                                                                    title="LinkedIn"
                                                                    type="text"
                                                                    name="LinkedIn"
                                                                    // value={inkedIn}
                                                                    // onChange={setLinkedIn}
                                                                    placeholder="linkedIn"
                                                                />
                                                            }
                                                            right={
                                                                <Input 
                                                                title="Youtube"
                                                                type="text"
                                                                name="youtube"
                                                                // value={youtube}
                                                                // onChange={setYoutube}
                                                                placeholder="Youtube"
                                                            />
                                                            }
                                                        />

                                                        <Input 
                                                            title="Instagram"
                                                            type="text"
                                                            name="instagram"
                                                            // value={instagram}
                                                            // onChange={setInstagram}
                                                            placeholder="Instagram"
                                                        /> 

                                                        <div className="popup-type-margin">Bio</div>
                                                        <textarea 
                                                            className="input"
                                                            placeholder="Write a bio..."
                                                            // value={post.description}
                                                            // onChange={e => updatePostValue('description', e.target.value)}
                                                            maxLength={350}
                                                        ></textarea>
                                                    </div>
                                                </PopupBodyDefault>
                                            }
                                            footer={
                                                <PopupFooterDoubleButton
                                                    onCancel={handleUpdateCancel}
                                                    onClick={(e)=> {submitUser(e)}}
                                                    name='Create'
                                                />
                                            }
                                        /> 
                                    </div>
                                : 

                                    <div className="popup-width">
                                        <Popup
                                            body={
                                                <PopupBodyDefault>
                                                    <div>
                                                            <div className="isSynced">
                                                                <input 
                                                                    type="checkbox" 
                                                                    name="topping" 
                                                                    // value={isChecked} 
                                                                    checked={isChecked}  
                                                                    onChange={onCheckChange} 
                                                                />
                                                                is_synced
                                                            </div>

                                                        <div className={checkboxEvent}>
                                                            <ProfileImage/>

                                                            <Input 
                                                                title="User Name"
                                                                type="text"
                                                                name="name"
                                                                // value={name}
                                                                // onChange={setName}
                                                                placeholder="User name"
                                                            />
                                                            <Input 
                                                                title="Slug"
                                                                type="text"
                                                                name="url"
                                                                // value={slug}
                                                                // onChange={setSlug}
                                                                placeholder="Slug"
                                                            />

                                                            <Input 
                                                                title="Description"
                                                                type="text"
                                                                name="description"
                                                                // value={description}
                                                                // onChange={setDescription}
                                                                placeholder="Description"
                                                            />
                                                        </div>

                                                    </div>
                                                </PopupBodyDefault>
                                            }
                                            footer={
                                                <PopupFooterDoubleButton
                                                    onCancel={handleUpdateCancel}
                                                    onClick={(e)=> {submitUser(e)}}
                                                    name='Create'
                                                />
                                            }
                                        /> 
                                    </div>

                            : null
                        }
                    </div>


                    <div className="table-delete">
                        <span className={styleDeleteIcon} onClick={handleDelete}>
                            <Trash size={10} />
                        </span>
                        {
                            deletePopupOpened ?
                                <PopupConfirm
                                    title="Delete Permanently"
                                    text="Are you sure to delete this tag permanently? You will not be able to access it anymore."
                                    name="Delete"
                                    buttonClass="danger"
                                    onClick={handleDoDelete}
                                    onCancel={handleDeleteCancel}
                                />
                            : null
                        }
                    </div>
                    <div className="table-view">
                        <span className='table-button'>
                            <BoxArrowInRight size={10} />
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
        const tagId = lastValue.value
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