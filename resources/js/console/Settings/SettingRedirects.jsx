import React from 'react';

export default function SettingRedirects(props) {

    return <div className="setting-redirects">

        <div className="redirect-view">
            <div className="title">
                Redirects
            </div>
            <div className="redirects">

                <div>
                    <div className='redirect-create'>Create Redirect URL</div>
                    <form className='redirect-create'>
                        <input type="text" />
                    </form>
                </div>
                <div class="user-row">
                    <div className="user-left">
                        <div>Redirect URL</div>
                    </div>
                    <div className="user-right">
                        <div className="redirect-delete">Delete</div>
                        <div className="redirect-edit">Edit</div>
                    </div>
                </div>
                 
            </div>
        </div>

    </div>

}