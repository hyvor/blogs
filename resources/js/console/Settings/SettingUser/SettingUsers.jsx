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
import TextareaAutosize from 'react-textarea-autosize';

import Users from './UsersTable';
import CreateNewUser from './CreateNewUser';

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
                        {/* <div>
                            <button type='button' className ="loadMore">Load More</button>
                        </div> */}
                    </div>                            
                </div>
            }
        </div>
    </div> 
}