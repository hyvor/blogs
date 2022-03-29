import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, BoxArrowInRight, CodeSlash} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import subdomainLogic from '../logic/subdomainLogic';
import routesLogic from '../logic/routesLogic';
import Loader from '../ReusableComponents/Loader';
import Select from '../ReusableComponents/Select';
import Toast from '../ReusableComponents/Toast';
import NoResults from '../ReusableComponents/NoResults';
import Input from '../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../ReusableComponents/Popup';


export default function SettingsRoutes() {

    return <div className="settingRoute">
        <div className="route-title-bar">
            <div className="route-title">
                Routes
            </div>
            <div>
                <CreateNewRoute />
            </div>
        </div>
        <div>
            {
                <div className="global-table-view">
                    <div className="global-table-header-three">      
                        <div className="table-head-item">Route Name</div> 
                        <div className="table-head-item">Match</div>
                        <div></div>
                    </div>

                    <div>
                        <div className="global-table-body">
                            <Routes/>           
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

function CreateNewRoute() {

    const [createPopUpOpened, setCreatePopUpOpened] = useState(false);

    function handleCreateCancel(){
        setCreatePopUpOpened(false)
    }
    function handleCreate(e) {
        e.preventDefault();
        setCreatePopUpOpened(true);
    }

    return <div>
        {
            <div className="route-title-create-button ">
                <button type='button' className ="button small inactive route-button-popup" onClick={handleCreate}>
                    <div className="popup-button-content">
                        <div className="popup-button-content-text">Create</div> 
                        <Plus />
                    </div>
                </button>
            </div>
        }
        {
            createPopUpOpened ?
                <Popup
                    header={<PopupHeaderDefault title='Create New Route' />}
                    body={
                        <PopupBodyDefault>
                            <div>
                                <Input 
                                    title="Route name"
                                    type="text"
                                    name="name"
                                    // value={name}
                                    // onChange={setName}
                                    placeholder="Route name"
                                />
                                <Input 
                                    title="Match path"
                                    type="text"
                                    name="url"
                                    // value={slug}
                                    // onChange={setSlug}
                                    placeholder="Match path"
                                />
                            </div>
                        </PopupBodyDefault>
                    }
                    footer={
                        <PopupFooterDoubleButton
                            onCancel={handleCreateCancel}
                            // onClick={(e)=> {submitTag(e)}}
                            name='Create'
                        />
                    }
                /> 
            : null
        }
    </div>
}

function Routes(){
    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");

    const [updatePopUpOpened, setUpdatePopUpOpened] = useState(false);
    const [deletePopupOpened, setDeletePopupOpened] = useState(false);

    const [ routeName, setRouteName ] = useState('rasif');


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
        setDeletePopupOpened(false);
        setStyleDeleteIcon("table-button");
    }
    function handleDeleteCancel(){
        setStyleDeleteIcon("table-button");
        setDeletePopupOpened(false)
    }

    return <div>
        <div className="global-table-body">
            <div className="table-body-three">
                <div className="table-item"> {routeName}</div>
                <div className="table-item"> rasif-sahl </div>
                
                <div className="table-actions">
                    <div className="table-edit" >
                        <span className={styleUpdateIcon} onClick={handleUpdate}>
                            <PencilFill size={10} />
                        </span>

                        {
                            updatePopUpOpened ?
                                <div className="popup-width">
                                    <Popup
                                        header={ <PopupHeaderDefault title='Create New Route' /> }
                                        body={
                                            <PopupBodyDefault>
                                                <div>
                                                    <Input 
                                                        title="Route Name"
                                                        type="text"
                                                        name="name"
                                                        // value={name}
                                                        // onChange={setName}
                                                        placeholder="Route name"
                                                    />
                                                    <Input 
                                                        title="Match Path"
                                                        type="text"
                                                        name="url"
                                                        // value={slug}
                                                        // onChange={setSlug}
                                                        placeholder="Match Path"
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

                    {
                        routeName === ('post' || 'page' || 'Post' || 'Page') ? null
                        :
                            <div className="table-delete">
                                <span className={styleDeleteIcon} onClick={handleDelete}>
                                    <Trash size={10} />
                                </span>
                                {
                                    deletePopupOpened ?
                                        <PopupConfirm
                                            title="Delete Permanently"
                                            text="Are you sure to delete this route permanently? You will not be able to access it anymore."
                                            name="Delete"
                                            buttonClass="danger"
                                            onClick={handleDoDelete}
                                            onCancel={handleDeleteCancel}
                                        />
                                    : null
                                }
                            </div>
                    }


                </div>

            </div>
        </div>
    </div>
}