import React from 'react';

export default function SettingRedirects(props) {

    return <div className="setting-redirects">

        <div className="redirect-view">
            <div className="title">
                Redirects
            </div>
            <div className="ridirects">

                <div>
                    <div className='riderect-create'>Create Redirect URL</div>
                    <form className='riderect-create'>
                        <input type="text" />
                    </form>
                </div>
                <div class="user-row">
                    <div className="user-left">
                        <div>Redirect URL</div>
                    </div>
                    <div className="user-right">
                        <div className="riderect-delete">Delete</div>
                        <div className="riderect-edit">Edit</div>
                    </div>
                </div>
                 
            </div>
        </div>

    </div>

}