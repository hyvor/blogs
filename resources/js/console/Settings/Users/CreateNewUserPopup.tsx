import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Popup, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import Input, {InputView} from '../../ReusableComponents/Input';
import usersLogic from '../../logic/usersLogic';
import getSubdomain from "../../logic-helpers/subdomain";
import Callout from "../../ReusableComponents/Callout";
import Select from "../../ReusableComponents/Select";
import {UserRole} from "../../enums";

export default function CreateNewUserPopup({onClose}: {onClose: Function}) {

    const usersLogicInst = usersLogic({subdomain: getSubdomain()})
    const { createAjax } = useValues(usersLogicInst)
    const { create, createGuest } = useActions(usersLogicInst)

    const [ userType, setUserType ] = useState<'guest' | 'hyvor'>('hyvor')

    const [ usernameOrEmail, setUsernameOrEmail ] = useState('');
    const [usernameOrEmailError, setUsernameOrEmailError] = useState<null | string>(null);
    const [ role, setRole ] = useState<UserRole>(UserRole.ADMIN);
    const [ guestName, setGuestName ] = useState('');
    const [guestNameError, setGuestNameError] = useState<null | string>(null);

    const roleOptions = [
        { label: 'Admin', value: 'admin'},
        { label: 'Editor', value: 'editor' },
        { label: 'Writer', value: 'writer'},
        { label: 'Contributor', value: 'contributor' },
        { label: 'Finance', value: 'finance'}
    ];

    function handleCreate() {

        setUsernameOrEmailError(null);
        setGuestNameError(null);

        if (userType === 'hyvor') {

            if (usernameOrEmail.trim() === "") {
                return setUsernameOrEmailError('Cannot be empty');
            }

            create({usernameOrEmail, role, onCreate: onClose})

        } else {

            if (guestName.trim() === "") {
                return setGuestNameError('Cannot be empty');
            }

            createGuest({name: guestName, onCreate: onClose})

        }

    }

    return <Popup
        header={<PopupHeaderDefault title="Add User" />}
        body={
            <div className="user-create-popup">
                <div className="user-type">
                    <span
                        onClick={() => setUserType('hyvor')}
                        className={"button small" + (userType === 'hyvor' ? ' inactive' : ' text-only')}
                    >Hyvor User</span>
                    <span
                        onClick={() => setUserType('guest')}
                        className={"button small" + (userType === 'guest' ? ' inactive' : ' text-only')}
                    >Guest User</span>
                </div>
                <div className="user-data">
                    {
                        userType === 'hyvor' ?
                        <div>
                            <Callout
                                color="blue"
                                text={
                                    <div>
                                        Please ask the user to create a Hyvor account at <a
                                            href="https://hyvor.com/signup"
                                            target="_blank"
                                            className="link"
                                        >hyvor.com/signup</a> first. Then, add their username or email below.
                                    </div>
                                }
                            />
                            <Input
                                title="Username or Email"
                                type="text"
                                name="name"
                                value={usernameOrEmail}
                                onChange={setUsernameOrEmail}
                                error={usernameOrEmailError}
                            />
                            <InputView
                                title="Role"
                                content={
                                    <Select
                                        value={roleOptions.find(o => o.value === role)}
                                        options={roleOptions}
                                        onChange={(v: any) => setRole(v.value)}
                                    />
                                }
                            />
                        </div>
                        :
                        <div>
                            <Callout
                                color="orange"
                                text="Guest users cannot access the Console."
                            />
                            <Input
                                title="Name"
                                type="text"
                                name="name"
                                value={guestName}
                                onChange={setGuestName}
                                error={guestNameError}
                            />
                        </div>
                    }
                </div>
            </div>
        }
        footer={
            <PopupFooterDoubleButton
                onCancel={onClose}
                onClick={handleCreate}
                name="Create"
                isLoading={createAjax.status === 'loading'}
            />
        }
    />

}