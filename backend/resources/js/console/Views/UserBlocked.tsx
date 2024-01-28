import React from 'react';
import Button from "../ReusableComponents/Button";
import NoResults from "../ReusableComponents/NoResults";

export default function UserBlocked() {

    return <div className="user-blocked">

        <NoResults 
            text={
                <div>
                    Seems like we are having some issues with your account. <br />
                    Please contact support.
                </div>
            }
            padding={20}
        />

        <div>
            <a className="button" href="/">Go to homepage</a>
        </div>

    </div>

}