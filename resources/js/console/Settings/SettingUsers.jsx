import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, ArrowBarRight} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import subdomainLogic from '../logic/subdomainLogic';
import usersLogic from '../logic/usersLogic';
import Loader from '../ReusableComponents/Loader';
import Select from '../ReusableComponents/Select';
import Toast from '../ReusableComponents/Toast';
import NoResults from '../ReusableComponents/NoResults';
import Input from '../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../ReusableComponents/Popup';


export default function SettingUsers(props) {

    return <div className="settingsTag">
        <div className="tag-title-bar">
            <div className="tag-title">
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
                        <div className="table-head-item">Description</div>
                        <div className="table-head-item">Posts</div>
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
    return <div>
        <div className="tag-title-create-button ">
            <button type='button' className ="button small inactive tag-button-popup">
                <div className="popup-button-content">
                    <div className="popup-button-content-text">Create</div> 
                    <Plus />
                </div>
            </button>
        </div>
    </div>
}

function Users (){

    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");

    return <div>
        <div className="global-table-body">
            <div className="table-body-five">
                <div className="table-item"> Rasif</div>
                <div className="table-item"> Sahl </div>
                <div className="table-item"> Test</div>
                <div className="table-item"> 2000 </div>
                <div className="table-actions">
                    <div className="table-edit" >
                        <span className={styleUpdateIcon}>
                            <PencilFill size={10} />
                        </span>
                    </div>
                    <div className="table-delete">
                        <span className={styleDeleteIcon}>
                            <Trash size={10} />
                        </span>
                
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