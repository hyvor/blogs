import subdomainLogic from "../../logic/subdomainLogic";
import redirectsLogic from "../../logic/redirectsLogic";
import {useActions, useValues} from "kea";
import React, {useState} from "react";
import {Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import Input, {InputView} from "../../ReusableComponents/Input";
import {Redirect} from "../../types";
import Select, {SelectOption} from "../../ReusableComponents/Select";
import {toast} from "react-toastify";

export default function CreateUpdateRedirectPopup(
    { redirect = {} as Redirect, onClose } : { redirect?: Redirect, onClose: Function }
) {

    const isCreate = !redirect.id;

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { create, update } = useActions(redirectLogicBuilt)
    const { createAjax, updateAjax } = useValues(redirectLogicBuilt)

    const [ path, setPath ] = useState<string>(redirect.path || '');
    const [ to, setTo ] = useState(redirect.to || '');
    const [ type, setType ] = useState(redirect.type || 'permanent')

    const typeOptions : SelectOption[] = [
        { label: 'Permanent', value: 'permanent' },
        { label: 'Temporary', value: 'temporary'}
    ];

    function handleClick() {
        if (path.match(/^https?:\/\//)) {
            return toast.error("Path should be relative");
        }

        let pathNew = path[0] !== '/' ? '/' + path : path;
        isCreate ?
            create({path: pathNew, to, type, onCreate: onClose}) :
            update({id: redirect.id, path: pathNew, to, type, onUpdate: onClose})

    }

    return <Popup
        header={<PopupHeaderDefault title={isCreate ? "Create Redirect" : "Update Redirect"} />}
        body={
            <PopupBodyDefault>
                <div>
                    <Input
                        title="From"
                        type="text"
                        name="name"
                        value={path}
                        onChange={value => setPath(value)}
                        placeholder="/welcome"
                        autoFocus={true}
                    />
                    <Input
                        title="To"
                        type="text"
                        name="url"
                        value={to}
                        onChange={value => setTo(value)}
                        placeholder="https://hyvor.com"
                    />
                    <InputView title="Type" content={
                        <Select
                            options={typeOptions}
                            defaultValue={typeOptions.find(o => o.value === type)}
                            onChange={(v: SelectOption) => setType(v.value)}
                        />
                    } />
                </div>
            </PopupBodyDefault>
        }
        footer={
            <PopupFooterDoubleButton
                onCancel={onClose}
                onClick={handleClick}
                name='Create'
                isLoading={createAjax.status === 'loading' || updateAjax.status === 'loading'}
                loadingName={isCreate ? "Creating" : "Updating"}
            />
        }
    />;

}