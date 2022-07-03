import React, {useState} from 'react';
import {useValues} from 'kea';
import {PencilFill, Trash} from 'react-bootstrap-icons';
import languagesLogic from '../../logic/languagesLogic';
import {User as UserType} from "../../types";
import getSubdomain from "../../logic-helpers/subdomain";
import {TableRow, TableRowItem} from "../../ReusableComponents/Table";
import {UserRole, UserStatus} from "../../enums";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import {useUsersActions} from "../../logic-helpers/users";
import {toast} from "react-toastify";
import UpdateUserPopup from "./UpdateUserPopup";

export default function User({user} : {user: UserType} ) {

    const { resendInvite, remove } = useUsersActions()

    const { primaryLanguage } = useValues(languagesLogic({subdomain: getSubdomain()}))

    const [ isUpdating, setIsUpdating ] = useState(false);
    const [ isDeleting, setIsDeleting ] = useState(false);

    const [ isResendingEmail, setIsResendingEmail ] = useState(false);

    function handleResend() {
        resendInvite({id: user.id});
        toast.success('Email sent');
        setIsResendingEmail(false)
    }

    function handleDelete() {
        remove({id: user.id})
        toast("User removed")
        setIsDeleting(false)
    }

    const variant = user.variants.find(v => v.language_id === primaryLanguage.id);

    return <TableRow>
        <TableRowItem>
            <div>{ variant?.name }</div>
            <div className="slug-row">
                <a href={ variant?.url } target="_blank" className="link">{ user.slug }</a>
            </div>
        </TableRowItem>
        <TableRowItem>
            <span className="status-tag">
                {
                    !user.hyvor_user_id && <span className="global-tag orange">GUEST</span>
                }
                {
                    user.hyvor_user_id && user.status === UserStatus.ACTIVE && <span className="global-tag green">ACTIVE</span>
                }
                {
                    user.hyvor_user_id && user.status === UserStatus.INVITED &&
                    <span>
                        <span className="global-tag blue">PENDING</span><br />
                        <a className="link resend-email" onClick={() => setIsResendingEmail(true)}>Resend Email</a>
                    </span>
                }
                {
                    user.hyvor_user_id && user.status === UserStatus.BLOCKED && <span className="global-tag">BLOCKED</span>
                }
            </span>
        </TableRowItem>
        <TableRowItem>
            {
                user.hyvor_user_id &&
                <div className="global-tag-role">{  user.role }</div>
            }
        </TableRowItem>
        <TableRowItem>{ user.posts_count }</TableRowItem>
        <TableRowItem>
            <button className="icon-button" onClick={() => setIsUpdating(true)}><PencilFill size={10} /></button>
            {
                user.role !== UserRole.OWNER &&
                <button className="icon-button" onClick={() => setIsDeleting(true)}><Trash size={10} /></button>
            }
        </TableRowItem>

        {
            isResendingEmail && <PopupConfirm
                title="Resend Invitation"
                text="Please confirm to re-send an invitation to this user."
                name="Resend"
                onClick={handleResend}
                onCancel={() => setIsResendingEmail(false)}
            />
        }

        {
            isUpdating && <UpdateUserPopup user={user} onClose={() => setIsUpdating(false)} />
        }

        {
            isDeleting &&
            <PopupConfirm
                title="Remove User"
                text={
                    <div>
                        <p>
                            Please confirm to remove this user from the blog.
                        </p>
                        <ul>
                            <li>The user will be removed as an author from all posts.</li>
                            <li>Posts created by this user will not be deleted.</li>
                        </ul>
                    </div>
                }
                name="Remove"
                buttonClass="danger"
                onClick={handleDelete}
                onCancel={() => setIsDeleting(false)}
            />
        }

    </TableRow>

}