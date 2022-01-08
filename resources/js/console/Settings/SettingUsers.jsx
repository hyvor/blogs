import React from 'react';

export default function SettingUsers(props) {

    return <div className="setting-users">

        <div className="users-view">
            <div className="title">
                Users
            </div>
            <div className="users">
                { 
                    [1,2,3,4,5,6].map((i) => {
                        return <div class="user-row" key={i}>
                            <div className="user-left">
                                <img src="https://picsum.photos/200/200"></img>
                                <span className="user-name-email">
                                    <span className="user-name">Supun</span>
                                    <span className="user-hyvor">@supun</span>
                                </span>
                            </div>
                            <div className="user-right">
                                <div className="user-role">Owner</div>
                            </div>
                        </div>
                    })
                }   
            </div>
        </div>

    </div>

}