import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, BoxArrowInRight} from 'react-bootstrap-icons';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import Input from '../../ReusableComponents/Input';
import ReactSelect, { components } from 'react-select';
import DualSetting from '../../ReusableComponents/DualSetting';
import subdomainLogic from '../../logic/subdomainLogic';
import usersLogic from '../../logic/usersLogic';

import {toast} from 'react-toastify'
import Loader from '../../ReusableComponents/Loader';
import Toast from '../../ReusableComponents/Toast';
import NoResults from '../../ReusableComponents/NoResults';
import ProfileImage from '../../ReusableComponents/ProfileImage';
import TextareaAutosize from 'react-textarea-autosize';



export default function CreateNewUser(props) {

    const subdomain = subdomainLogic.values.subdomain;
    const usersLogicBuilt = usersLogic({subdomain})
    const { create } = useActions(usersLogicBuilt) 

    const [createPopUpOpened, setCreatePopUpOpened] = useState(false);
    const [ settingsType, setSettingsType ] = useState('Guest User'); // Guest User | Hyvor User

    const [slug, setSlug] = useState('');
    const [slugError, setSlugError] = useState(null);
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');

    const [status, setStatus] = useState({statusType: ""});
    const [role, setRole] = useState({type: ""});


    function handleRole({value}){
        setRole({...role, type: value})
        console.log('redirect test')
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


    function onNameChange(val) {
        setName(val);
        var slug = val.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/(^-|-$)/g, '');
        setSlug(slug);
    }

    function onSlugChange(val){
        val = val.toLowerCase();
        setSlug(val);

        var allowedRegex = /[^a-z0-9-]/;

        if (val.substr(0, 1) === '-') {
            setSlugError('Cannot start with -');
        } else if (val.substr(val.length - 1) === '-') {
            setSlugError('Cannot end with -');
        } else if (val.match(allowedRegex)) {
            const firstLetter = val.match(allowedRegex)[0]
            setSlugError('Cannot contain ' + firstLetter);
        } else {
            setSlugError(null)
        }
    }

    function handleCreateCancel(){
        setCreatePopUpOpened(false)
    }
    function handleCreate(e) {
        e.preventDefault();
        setCreatePopUpOpened(true);
    }
    function submitUser (e) {
        e.preventDefault();
        console.log(name)
        console.log(role.type)
        console.log(status.statusType)
        create({
            name: name,
            email: email,
            slug: slug,
            role: role.type,
            status: status.statusType
        });
        setName();
        setEmail();
        setSlug();
        
        setCreatePopUpOpened(false)
    }

    return <div>
        <div className="user-title-create-button ">
            <button type='button' className ="button small inactive user-button-popup">
                <div className="popup-button-content" onClick={handleCreate}>
                    <div className="popup-button-content-text">Add User</div> 
                    <Plus />
                </div>
            </button>
        </div> 
        {
            createPopUpOpened ?
            
            <div className="popup-width">

                <Popup
                    header={
                        <div>
                            <PopupHeaderDefault title='Add User' />
                                <div className="create-select">
                                    <span 
                                        onClick={() => setSettingsType('Guest User')} 
                                        className={settingsType === 'Guest User' ? 'active' : ''}
                                    >Guest User</span>
                                    <span 
                                        onClick={() => setSettingsType('Hyvor User')} 
                                        className={settingsType === 'Hyvor User' ? 'active' : ''}
                                    >Hyvor User</span>
                                </div>
                        </div>
                    }
                    
                    body={
                        <PopupBodyDefault>
                            {
                                settingsType === 'Guest User' ?
                                    <div>
                                        <Input 
                                            title="Guest Name"
                                            type="text"
                                            name="name"
                                            value={name}
                                            onChange={onNameChange}
                                            placeholder="Guest name"
                                        />
                                        <Input 
                                            title="Slug"
                                            type="text"
                                            name="url"
                                            value={slug}
                                            onChange={onSlugChange}
                                            placeholder="Slug"
                                        />
                                        <Input 
                                            title="Email"
                                            type="text"
                                            name="email"
                                            value={email}
                                            onChange={setEmail}
                                            placeholder="Description"
                                        />
                                        <DualSetting 
                                            left={
                                                <div>
                                                    <div className="popup-type-margin">Status</div>
                                                        <SelectUserRole  options = {selectStatus} onChange = {handleStatus}/>
                                                </div>
                                            }
                                            right={
                                                <div>
                                                    <div className="popup-type-margin">Role</div>
                                                        <SelectUserRole  options = {selectRole} onChange = {handleRole} defaultValue={handleRole[0]}/>
                                                </div>
                                            }
                                        />                               
                                    </div>
                                : 
                                <div>
                                    <Input 
                                        title="User Name Or Email"
                                        type="text"
                                        name="name"
                                        // value={name}
                                        // onChange={setName}
                                        placeholder="User name or email"
                                    />
                
                                    <div className="popup-type-margin">Role</div>
                                    <SelectUserRole  options = {selectRole}/>
                
                                    <div className="popup-type-margin margin-top">Status</div>
                                    <SelectUserRole  options = {selectStatus}/>
                                </div>                   
                            }
                        </PopupBodyDefault>
                    }
                    footer={
                        <PopupFooterDoubleButton
                            onCancel={handleCreateCancel}
                            onClick={(e)=> {submitUser(e)}}
                            name='Add'
                        />
                    }
                /> 
            </div>
            : null
        }
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
            onChange={onChange}
        />
    );

    return <SelectUserRole/>

}