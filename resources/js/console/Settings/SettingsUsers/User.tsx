import React, {useState, useEffect} from 'react';
import { useActions, useValues } from 'kea';
import {Trash, PencilFill, Plus, BoxArrowInRight, Link, Link45deg} from 'react-bootstrap-icons';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import ProfileImage from '../../ReusableComponents/ProfileImage';
import ReactSelect, { components } from 'react-select';
import DualSetting from '../../ReusableComponents/DualSetting';
import Input from '../../ReusableComponents/Input';
import languagesLogic from '../../logic/languagesLogic';
import blogsLogic from '../../logic/blogsLogic';
import UserLanguageSelector from './UserLanguageSelector';
import usersLogic from '../../logic/usersLogic';

import {toast} from 'react-toastify'
import {User, UserVariant} from "../../types";
import subdomainLogic from "../../logic/subdomainLogic";
import UpdateUserPopup from "./UpdateUserPopup";


export default function User({user} : {user: User} ) {

    /*const usersLogicBuilt = usersLogic({subdomain})
    const { remove , updateData } = useActions(usersLogicBuilt)
    const { /!*updateDataAjax*!/ } = useValues(usersLogicBuilt)*/

    const { primaryLanguage } = useValues(languagesLogic({subdomain: subdomainLogic.values.subdomain}))

    const [ isUpdating, setIsUpdating ] = useState<boolean>(false);

    return <div className="global-table-body">
        <div className="table-body-five">
            <div className="table-item">{ user.variants[primaryLanguage.id].name }</div>
            <div className="table-item">{ user.slug }</div>
            <div className="table-item">{ user.email }</div>
            <div className="table-item">
                <div className="global-tag-role">{ user.role }</div>
            </div>
            <div className="table-actions">
                <span className="table-button" onClick={() => setIsUpdating(true)}>
                    <PencilFill size={10} />
                </span>

                    { isUpdating ? <UpdateUserPopup
                        user={user}
                        onClose={() => setIsUpdating(false)}
                    /> : null }

                <a className="table-button">
                    <Link45deg size={10} />
                </a>
            </div>
        </div>
    </div>

}