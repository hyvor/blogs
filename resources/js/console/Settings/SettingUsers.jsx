import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, ArrowBarRight} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import subdomainLogic from '../logic/subdomainLogic';
import usersLogic from '../logic/usersLogic';
import Loader from '../ReusableComponents/Loader';
import Toast from '../ReusableComponents/Toast';
import NoResults from '../ReusableComponents/NoResults';
import Input from '../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../ReusableComponents/Popup';
import FormDualSetting from '../ReusableComponents/FormDualSetting';
import ProfileImage from '../ReusableComponents/ProfileImage';




export default function SettingUsers(props) {

    return <div className="settingUser">
        <div className="user-title-bar">
            <div className="user-title">
                Users
            </div>
            <div>
                <CreateNewUser />
            </div>
        </div>

        <div>
            {
                <div className="global-table-view">
                    <div className="global-table-header-five">      
                        <div className="table-head-item">Name</div> 
                        <div className="table-head-item">Slug</div>
                        <div className="table-head-item">Email</div>
                        <div className="table-head-item">Role</div>
                        <div></div>
                    </div>

                    <div>
                        <div className="global-table-body">
                            <Users/>           
                        </div>                                   
                        <div>
                            <button type='button' className ="loadMore">Load More</button>
                        </div>
                    </div>                            
                </div>
            }
        </div>
    </div> 

}

function CreateNewUser(){

    const [createPopUpOpened, setCreatePopUpOpened] = useState(false);

    function handleCreateCancel(){
        setCreatePopUpOpened(false)
    }
    function handleCreate(e) {
        e.preventDefault();
        setCreatePopUpOpened(true);
    }
    function submitUser (e) {
        e.preventDefault();
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
                    header={<PopupHeaderDefault title='Add User' />}
                    body={
                        <PopupBodyDefault>
                            <div>
                                <Input 
                                    title="User Name"
                                    type="text"
                                    name="name"
                                    // value={name}
                                    // onChange={setName}
                                    placeholder="Tag name"
                                />
                                <Input 
                                    title="Slug"
                                    type="text"
                                    name="url"
                                    // value={slug}
                                    // onChange={setSlug}
                                    placeholder="SLug"
                                />
                                <FormDualSetting 
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
                                        <Input 
                                            title="Role"
                                            type="text"
                                            name="role"
                                            // value={description}
                                            // onChange={setDescription}
                                            placeholder="Description"
                                        />
                                    }
                                />
                            </div>
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

function Users (){

    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");

    const [updatePopUpOpened, setUpdatePopUpOpened] = useState(false);
    const [deletePopupOpened, setDeletePopupOpened] = useState(false);

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
                                <div className="popup-width">
                                    <Popup
                                        body={
                                            <PopupBodyDefault>
                                                <div>
                                                    <ProfileImage/>

                                                    <Input 
                                                        title="User Name"
                                                        type="text"
                                                        name="name"
                                                        // value={name}
                                                        // onChange={setName}
                                                        placeholder="Tag name"
                                                    />
                                                    <Input 
                                                        title="Slug"
                                                        type="text"
                                                        name="url"
                                                        // value={slug}
                                                        // onChange={setSlug}
                                                        placeholder="SLug"
                                                    />

                                                    <FormDualSetting 
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
                                                            <Input 
                                                                title="Role"
                                                                type="text"
                                                                name="role"
                                                                // value={description}
                                                                // onChange={setDescription}
                                                                placeholder="Description"
                                                            />
                                                        }
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
                            <ArrowBarRight size={10} />
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

}