import React, {useEffect, useState} from 'react';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../../logic/subdomainLogic';
import usersLogic from '../../logic/usersLogic';
import Loader from '../../ReusableComponents/Loader';
import Toast from '../../ReusableComponents/Toast';
import NoResults from '../../ReusableComponents/NoResults';
import CreateNewUser from './CreateNewUser';
import User from "./User";

export default function SettingsUsers() {

    const subdomain = subdomainLogic.values.subdomain;
    const usersLogicBuilt = usersLogic({subdomain})
    const { usersList, users, loadAjax, createAjax, loadMoreAjax } = useValues(usersLogicBuilt)
    const { load, loadMore } = useActions(usersLogicBuilt)

    useEffect(() => {
        load();
    }, [])


    return <div className="settings-users">

        <div className="title">
            Users
            <span>
                <CreateNewUser />
                {
                    createAjax.status === 'error' ?
                    <Toast
                        text={createAjax.error}
                        type="error"
                    /> : null
                }
            </span>
        </div>

        <div>
            {
                loadAjax.status === 'loading' ?
                    <Loader padding={40}/> 
                :
                    <div>
                        {
                            usersList.length ?
                                <div className="global-table-view">
                                    <div className="global-table-header-five">      
                                        <div className="table-head-item">Name</div> 
                                        <div className="table-head-item">Slug</div>
                                        <div className="table-head-item">Email</div>
                                        <div className="table-head-item">Role</div>
                                        <div />
                                    </div>

                                    <div>
                                        <div className="global-table-body">
                                            {
                                                usersList.map(userId => {
                                                    const user = users[userId];
                                                    return <div
                                                        key={userId}
                                                        className="global-table-body"
                                                    >
                                                        <User user={user} />
                                                    </div>
                                                })
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