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
import getSubdomain from "../../logic-helpers/subdomain";
import {TableRow, TableRowItem} from "../../ReusableComponents/Table";


export default function User({user} : {user: User} ) {

    /*const usersLogicBuilt = usersLogic({subdomain})
    const { remove , updateData } = useActions(usersLogicBuilt)
    const { /!*updateDataAjax*!/ } = useValues(usersLogicBuilt)*/

    const { primaryLanguage } = useValues(languagesLogic({subdomain: getSubdomain()}))

    const [ isUpdating, setIsUpdating ] = useState(false);
    const [ isDeleting, setIsDeleting ] = useState(false);

    return <TableRow>
        <TableRowItem>{ user.variants[primaryLanguage.id].name }</TableRowItem>
        <TableRowItem>{ user.slug }</TableRowItem>
        <TableRowItem>{ user.posts_count }</TableRowItem>
        <TableRowItem>
            <div className="global-tag-role">{ user.role }</div>
        </TableRowItem>
        <TableRowItem>
            <button className="icon-button" onClick={() => setIsUpdating(true)}><PencilFill size={10} /></button>
            <button className="icon-button" onClick={() => setIsDeleting(true)}><Trash size={10} /></button>
        </TableRowItem>
    </TableRow>

}