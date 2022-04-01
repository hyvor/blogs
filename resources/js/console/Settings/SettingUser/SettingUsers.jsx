import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../../logic/subdomainLogic';
import usersLogic from '../../logic/usersLogic';
import Loader from '../../ReusableComponents/Loader';
import Toast from '../../ReusableComponents/Toast';
import NoResults from '../../ReusableComponents/NoResults';
import Users from './UsersTable';
import CreateNewUser from './CreateNewUser';


import { Trash, PencilFill, Plus, BoxArrowInRight} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import Input from '../../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import ProfileImage from '../../ReusableComponents/ProfileImage';
import ReactSelect, { components } from 'react-select';
import DualSetting from '../../ReusableComponents/DualSetting';
import TextareaAutosize from 'react-textarea-autosize';

//  Remaining 
/*
*
* Create User Variant in language select if a variant is not selected.
* Update User data in language select
* Delete User according to an condition.
* hyvor User or Guest User filter.
* Setting up image upload.
* Testing the select option error.
* 
*/

export default function SettingUsers(props) {
    const subdomain = subdomainLogic.values.subdomain;
    const usersLogicBuilt = usersLogic({subdomain})
    const { user, loadAjax, createAjax, loadTagsListMoreAjax } = useValues(usersLogicBuilt)
    const { loadTagsListMore, load} = useActions(usersLogicBuilt)


    return <div className="settingUser">
        <div className="user-title-bar">
            <div className="user-title">
                Users
            </div>
            <div>
                <CreateNewUser />
                {
                    createAjax.status === 'error' ?
                    <Toast 
                        x={console.log(createAjax.error)}
                        text={createAjax.error}
                        type="error"
                    /> : null
                }
            </div> 
        </div>

        <div>
            {
                loadAjax.status === 'loading' ?
                    <Loader padding={40}/> 
                :
                    <div>
                        {
                            user.length ?
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
                                            {
                                                user.map(user => (
                                                     <div className="global-table-body">
                                                        <Users key = {user} user={user} subdomain ={subdomain}/>           
                                                    </div>
                                                ))
                                            }          
                                        </div>                                   
                                        {/* <div>
                                            <button type='button' className ="loadMore">Load More</button>
                                        </div> */}
                                    </div>                            
                                </div>

                            :
                            <NoResults 
                                text="There are no users"
                                padding={40}
                                imageWidth={250}
                            />
                        }
                    </div>
            }
        </div>
    </div> 
}